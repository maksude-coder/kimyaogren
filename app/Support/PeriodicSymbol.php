<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Builds the two-letter "element symbol" shown inside a periodic-cell badge
 * (design system § 01), e.g. "Asitler, Bazlar ve Tuzlar" → "Ab".
 */
final class PeriodicSymbol
{
    /**
     * First letter of the first two words; a single word yields its first
     * two letters. Casing follows Turkish rules (i → İ, I → ı).
     */
    public static function for(string $name): string
    {
        preg_match_all('/\p{L}+/u', $name, $matches);
        $words = $matches[0];

        if ($words === []) {
            return '';
        }

        $letters = count($words) >= 2
            ? mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1)
            : mb_substr($words[0], 0, 2);

        return self::upper(mb_substr($letters, 0, 1)).self::lower(mb_substr($letters, 1, 1));
    }

    private static function upper(string $letter): string
    {
        return mb_strtoupper(strtr($letter, ['i' => 'İ', 'ı' => 'I']));
    }

    private static function lower(string $letter): string
    {
        return mb_strtolower(strtr($letter, ['I' => 'ı', 'İ' => 'i']));
    }
}
