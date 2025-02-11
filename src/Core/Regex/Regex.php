<?php

namespace App\Core\Regex;

class Regex
{
    public const string REGEX_AVAILABLE_MODIFIERS = 'imsxADUXJunr';
    public const string REGEX_FORMAT_REGEX = '/^(.{3,})([' . self::REGEX_AVAILABLE_MODIFIERS . ']*)$/';
    public const string REGEX_AVAILABLE_SINGULAR_DELIMITER_REGEX = '/^[^a-zA-Z-0-9\\\\ ]$/';
    public const array REGEX_AVAILABLE_DISSIMILAR_DELIMITERS = [['{', '}'], ['[', ']'], ['(', ')'], ['<', '>']];


    public static function isRegex(string $pattern): bool
    {
        if (!static::checkRegexFormat($pattern)) {
            return false;
        }

        return @preg_match($pattern, '') !== false;
    }

    /**
     * check only delimiters and length without modifiers more or equal 3
     */
    public static function checkRegexFormat(string $pattern): bool
    {
        if (preg_match(static::REGEX_FORMAT_REGEX, $pattern, $matches) === 1) {
            $startDelimiter = $matches[1][0];
            $endDelimiter = $matches[1][-1];

            if ($startDelimiter === $endDelimiter) {
                return preg_match(static::REGEX_AVAILABLE_SINGULAR_DELIMITER_REGEX, $startDelimiter) === 1;
            }

            foreach (static::REGEX_AVAILABLE_DISSIMILAR_DELIMITERS as $dissimilarDelimiter) {
                if ($dissimilarDelimiter[0] === $startDelimiter && $dissimilarDelimiter[1] === $endDelimiter) {
                    return true;
                }
            }
        }

        return false;
    }
}