<?php

namespace App\Core\FileSystem\Scanner\Iterators;

class PathFilterIterator extends RegexFilterIterator
{
    public function accept(): bool
    {
        return $this->isAccepted($this->current()->getPathname());
    }
}