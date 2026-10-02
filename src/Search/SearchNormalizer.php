<?php

namespace App\Search;

/**
 * Folds names so that "Te Waza", "te-waza" and "TEWAZA" all compare equal.
 * Kanji pass through unchanged, so "踵" still finds Kibisu-gaeshi.
 */
final class SearchNormalizer
{
    public static function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        if (class_exists(\Normalizer::class)) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_KD) ?: $text;
            $text = preg_replace('/\p{Mn}+/u', '', $text) ?? $text;
        }

        // Keep "|" as a field separator so a query cannot match across two fields.
        return preg_replace('/[\s\-_\'’.·]+/u', '', $text) ?? $text;
    }
}
