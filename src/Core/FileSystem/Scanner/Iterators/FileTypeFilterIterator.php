<?php

namespace App\Core\FileSystem\Scanner\Iterators;

class FileTypeFilterIterator extends \FilterIterator
{
    public const int FILES = 1;
    public const int DIRECTORIES = 2;

    public function __construct(\Iterator $iterator, protected int $mode = 0)
    {
        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        /**
         * @var \SplFileInfo $fileInfo
         */
        $fileInfo = $this->current();

        if (
            (($this->mode & static::FILES) !== static::FILES && $fileInfo->isFile()) ||
            (($this->mode & static::DIRECTORIES) !== static::DIRECTORIES && $fileInfo->isDir())
        ) {
            return false;
        }

        return true;
    }
}