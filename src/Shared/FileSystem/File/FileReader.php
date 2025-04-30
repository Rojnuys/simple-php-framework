<?php

declare(strict_types=1);

namespace App\Shared\FileSystem\File;

use App\Shared\FileSystem\File\Exceptions\ReadFileException;
use App\Shared\FileSystem\File\Interfaces\IFileReader;

readonly class FileReader implements IFileReader
{
    public function __construct(public string $path)
    {
    }

    /**
     * @throws ReadFileException
     */
    public function readAll(): string
    {
        try {
            $file = fopen($this->path, 'r');
             return fread($file, filesize($this->path));
        } catch (\Throwable $e) {
            throw new ReadFileException($e->getMessage());
        } finally {
            fclose($file);
        }
    }

    /**
     * @throws ReadFileException
     */
    public function readByLine(?callable $lineHandlerClb = null): \Generator
    {
        try {
            $file = fopen($this->path, 'r');

            while (false !== ($line = fgets($file))) {
                yield isset($lineHandlerClb) ? $lineHandlerClb($line) : $line;
            }
        } catch (\Throwable $e) {
            throw new ReadFileException($e->getMessage());
        } finally {
            fclose($file);
        }
    }
}