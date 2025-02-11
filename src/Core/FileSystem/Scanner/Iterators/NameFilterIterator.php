<?php

namespace App\Core\FileSystem\Scanner\Iterators;

class NameFilterIterator extends RegexFilterIterator
{
    public function accept(): bool
    {
        return $this->isAccepted($this->current()->getFilename());
    }
}