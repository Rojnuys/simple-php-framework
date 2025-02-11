<?php

namespace App\Core\FileSystem;

class Glob
{
    protected static function getNotEscapedCharPosition(string $string, string $char): int
    {
        $backslashInRow = 0;

        for ($i = 0; $i < strlen($string); $i++) {
            $current = $string[$i];

            if ($current === '\\') {
                $backslashInRow++;
                continue;
            }

            if ($backslashInRow % 2 === 0 && $current === $char) {
                return $i;
            }

            $backslashInRow = 0;
        }

        return -1;
    }

    public static function toRegex(string $glob) :string
    {
        $regex = '';
        $backslashInRow = 0;
        $inBrackets = false;
        $inCurlyBrackets = false;
        $inSquareBrackets = false;

        for ($i = 0; $i < strlen($glob); $i++) {
            $char = $glob[$i];

            if ($char === '\\') {
                $regex .= '\\';
                $backslashInRow++;
                continue;
            }

            if ($backslashInRow % 2 !== 0) {
                if (!$inSquareBrackets && $char === '^') {
                    throw new \InvalidArgumentException('Glob incorrect escaping. You can\'t escape ^ outside []');
                }
                if (!in_array($char, ['*', '?', '{', '}', '[', ']', ',', '^'])) {
                    throw new \InvalidArgumentException('Glob incorrect escaping. You can escape (* ? { } [ ] \\ ,) and ^ but only in []');
                }
                $regex .= $char;
                $backslashInRow = 0;
                continue;
            }

            if (!$inBrackets && $char === '{' && ($endBracketPosition = static::getNotEscapedCharPosition(substr($glob, $i + 1), '}')) !== -1) {
                if ($endBracketPosition === 0) {
                    throw new \InvalidArgumentException('Glob incorrect alternative syntax. {} can\'t be empty' );
                } else {
                    $regex .= '(?:';
                    $inBrackets = true;
                    $inCurlyBrackets = true;
                }
            } elseif (!$inBrackets && $char === '[' && ($endBracketPosition = static::getNotEscapedCharPosition(substr($glob, $i + 1), ']')) !== -1) {
                if ($endBracketPosition === 0 || ($endBracketPosition === 1 && $glob[$i + 1] === '^')) {
                    throw new \InvalidArgumentException('Glob incorrect syntax. [] or [^] can\'t be empty' );
                } else {
                    if (@preg_match('/[' . str_replace('[', '\\[', substr($glob, $i + 1, $endBracketPosition)) . ']/', '') === false) {
                        throw new \InvalidArgumentException('Glob incorrect group syntax ' . substr($glob, $i, $endBracketPosition + 1));
                    }
                    $regex .= '[';
                    $inBrackets = true;
                    $inSquareBrackets = true;
                }
            } elseif ($inCurlyBrackets && $char === '}') {
                $regex .= ')';
                $inBrackets = false;
                $inCurlyBrackets = false;
            } elseif ($inCurlyBrackets && $char === ',') {
                $regex .= '|';
            } elseif ($inSquareBrackets && $char === ']') {
                $regex .= ']{1}';
                $inBrackets = false;
                $inSquareBrackets = false;
            } elseif ($inSquareBrackets && $char === '^' && $glob[$i - 1] === '[') {
                $regex .= '^';
            } elseif (in_array($char, ['{', '}', '[', ']', '^', '$', '/', '|', '.', '(', ')', '+', '<', '>']) || ($inSquareBrackets && in_array($char, ['*', '?']))) {
                $regex .= '\\' . $char;
            } elseif ($char === '*' && isset($glob[$i + 1]) && $glob[$i + 1] === '*') {
                $regex .= '.*';
                $i++;
            } elseif ($char === '*') {
                $regex .= '[^\/\\\\]*';
            } elseif ($char === '?') {
                $regex .= '.';
            } else {
                $regex .= $char;
            }

            $backslashInRow = 0;
        }

        return '/^' . str_replace('\\\\', '\\\\\\\\', $regex) . '$/';
    }
}