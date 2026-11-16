<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Allow-list for question, solution, option and feedback HTML shown to
 * students (Sprint 3.2 plan § 2, rule 11): a few formatting tags, no
 * attributes at all.
 */
class QuestionHtml
{
    private const ALLOWED = ['p', 'br', 'b', 'strong', 'i', 'em', 'sub', 'sup', 'ol', 'ul', 'li'];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Drop the content of elements that must never be rendered as text either.
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', $html) ?? '';

        $html = strip_tags($html, self::ALLOWED);

        // What is left are allowed tags only; remove every attribute from them.
        $html = preg_replace('#<([a-z0-9]+)\b[^>]*?(/?)>#i', '<$1$2>', $html) ?? '';

        return trim($html);
    }

    /**
     * Plain text of a piece of question HTML, for labels and summaries.
     */
    public static function text(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5));
    }
}
