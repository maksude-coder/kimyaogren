<?php

declare(strict_types=1);

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Cleans kimyaogren.com WordPress post HTML for storage in theory_versions.
 *
 * - Keeps an allowlist of tags/attributes (the posts only use p, strong, b,
 *   br, hr, img, iframe; a few common text tags are allowed for safety).
 * - Iframes survive only for YouTube embeds.
 * - Upload URLs become root-relative "/wp-content/uploads/…" so the
 *   downloaded copies are served from the same path (ADR-004).
 */
final class WordPressContent
{
    private const UPLOADS_PATTERN = '#^(?:https?:)?//(?:www\.)?kimyaogren\.com(/wp-content/uploads/[^?\#]+)#i';

    /** @var array<string, list<string>> */
    private const ALLOWED = [
        'p' => [], 'br' => [], 'hr' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [],
        'sub' => [], 'sup' => [], 'ul' => [], 'ol' => [], 'li' => [], 'h2' => [], 'h3' => [], 'h4' => [],
        'blockquote' => [], 'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
        'a' => ['href'],
        'img' => ['src', 'alt', 'width', 'height'],
        'iframe' => ['src', 'title', 'width', 'height'],
    ];

    /** Elements removed together with their content. */
    private const DROP = ['script', 'style', 'noscript', 'form', 'object', 'embed'];

    public static function sanitize(string $html): string
    {
        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $document->getElementsByTagName('div')->item(0);
        self::cleanChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        // Drop empty paragraphs left behind by removed elements.
        $output = preg_replace('#<p>(?:\s|&nbsp;|\x{00A0})*</p>#u', '', $output) ?? $output;

        return trim($output);
    }

    /**
     * Root-relative "/wp-content/uploads/…" path for a kimyaogren.com upload URL, or null.
     */
    public static function uploadPath(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/wp-content/uploads/')) {
            return $url;
        }

        return preg_match(self::UPLOADS_PATTERN, trim($url), $m) === 1 ? rawurldecode($m[1]) : null;
    }

    /**
     * Every "/wp-content/uploads/…" image referenced by sanitized HTML.
     *
     * @return list<string>
     */
    public static function imagePaths(string $html): array
    {
        preg_match_all('#<img[^>]+src="(/wp-content/uploads/[^"]+)"#i', $html, $matches);

        return array_values(array_unique(array_map('html_entity_decode', $matches[1])));
    }

    public static function wordCount(string $html): int
    {
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $text) ?? $text);

        return $text === '' ? 0 : count(explode(' ', $text));
    }

    /**
     * Plain-text excerpt from WordPress's rendered excerpt ("… [&hellip;]").
     */
    public static function excerpt(string $html, int $limit = 300): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace(['/\[…\]|\[\.\.\.\]/u', '/\s+/u'], ['', ' '], $text) ?? $text);

        return mb_strlen($text) > $limit ? rtrim(mb_substr($text, 0, $limit - 1)).'…' : $text;
    }

    private static function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true) || ($tag === 'iframe' && ! self::isYouTube($child->getAttribute('src')))) {
                $node->removeChild($child);

                continue;
            }

            self::cleanChildren($child);

            if (! array_key_exists($tag, self::ALLOWED)) {
                // Unwrap unknown elements (span, div, …), keeping their content.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child, self::ALLOWED[$tag]);
        }
    }

    /**
     * @param  list<string>  $allowed
     */
    private static function cleanAttributes(DOMElement $element, array $allowed): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (! in_array($attribute->name, $allowed, true)) {
                $element->removeAttribute($attribute->name);
            }
        }

        $tag = strtolower($element->tagName);

        if ($tag === 'img') {
            $src = $element->getAttribute('src');
            $element->setAttribute('src', self::uploadPath($src) ?? $src);
            $element->setAttribute('loading', 'lazy');
            $element->setAttribute('decoding', 'async');
        }

        if ($tag === 'iframe') {
            $element->setAttribute('loading', 'lazy');
            $element->setAttribute('allowfullscreen', '');
        }

        if ($tag === 'a') {
            $href = $element->getAttribute('href');

            if (preg_match('#^(https?:|/|\#|mailto:)#i', $href) !== 1) {
                $element->removeAttribute('href');
            }
        }
    }

    private static function isYouTube(string $src): bool
    {
        return preg_match('#^https://(?:www\.)?youtube(?:-nocookie)?\.com/embed/#i', $src) === 1;
    }
}
