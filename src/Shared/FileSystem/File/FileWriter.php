<?php

declare(strict_types=1);

namespace App\Shared\FileSystem\File;

use App\Shared\FileSystem\File\Exceptions\WriteFileException;
use App\Shared\FileSystem\File\Interfaces\IFileWriter;

readonly class FileWriter implements IFileWriter
{
    public function __construct(public string $path)
    {
    }

    /**
     * @throws WriteFileException
     */
    public function rewrite(string $data): void
    {
        $this->write($data);
    }

    /**
     * @throws WriteFileException
     */
    public function append(string $data): void
    {
        $this->write($data, true);
    }

    /**
     * @throws WriteFileException
     */
    protected function write(string $data, bool $isAppend = false): void
    {
        try {
            $file = fopen($this->path, $isAppend ? 'a' : 'w');
            fwrite($file, $data);
        } catch (\Throwable $e) {
            throw new WriteFileException($e->getMessage());
        } finally {
            fclose($file);
        }
    }
}