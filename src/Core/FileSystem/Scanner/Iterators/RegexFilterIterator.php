<?php

namespace App\Core\FileSystem\Scanner\Iterators;

use App\Core\FileSystem\Glob;
use App\Core\Regex\Regex;

abstract class RegexFilterIterator extends \FilterIterator
{
    /**
     * @var string[]
     */
    protected array $matchRegularExpressions = [];
    /**
     * @var string[]
     */
    protected array $noMatchRegularExpressions = [];

    public function __construct(\Iterator $iterator, array $matchPatterns = [], array $noMatchPatterns = [])
    {
        foreach ($matchPatterns as $pattern) {
            $this->matchRegularExpressions[] = $this->toRegex($pattern);
        }

        foreach ($noMatchPatterns as $pattern) {
            $this->noMatchRegularExpressions[] = $this->toRegex($pattern);
        }

        parent::__construct($iterator);
    }

    protected function isAccepted(string $value): bool
    {
        foreach ($this->noMatchRegularExpressions as $notMatch) {
            if (preg_match($notMatch, $value) === 1) {
                return false;
            }
        }

        if (count($this->matchRegularExpressions) > 0) {
            foreach ($this->matchRegularExpressions as $match) {
                if (preg_match($match, $value) === 1) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    protected function toRegex(string $pattern): string
    {
        return Regex::isRegex($pattern) ? $pattern : Glob::toRegex($pattern);
    }
}