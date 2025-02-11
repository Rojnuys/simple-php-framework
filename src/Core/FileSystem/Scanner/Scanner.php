<?php

namespace App\Core\FileSystem\Scanner;

use App\Core\FileSystem\Scanner\Iterators\PathFilterIterator;
use App\Core\FileSystem\Scanner\Iterators\NameFilterIterator;
use App\Core\FileSystem\Scanner\Iterators\FileTypeFilterIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;

class Scanner implements \IteratorAggregate
{
    protected int $mode;
    /**
     * @var string[]
     */
    protected array $dirs;
    /**
     * @var string[]
     */
    protected array $paths;
    /**
     * @var string[]
     */
    protected array $notPaths;
    /**
     * @var string[]
     */
    protected array $names;
    /**
     * @var string[]
     */
    protected array $notNames;

    public function __construct()
    {
        $this->reset();
    }

    protected function reset(): void
    {
        $this->mode = 0;
        $this->dirs = [];
        $this->paths = [];
        $this->notPaths = [];
        $this->names = [];
        $this->notNames = [];
    }

    public function files(): static
    {
        $this->mode |= FileTypeFilterIterator::FILES;
        return $this;
    }

    public function directories(): static
    {
        $this->mode |= FileTypeFilterIterator::DIRECTORIES;
        return $this;
    }

    public function directoriesAndFiles(): static
    {
        $this->files();
        $this->directories();
        return $this;
    }

    protected function fillParameter(
        string       $paramName,
        string|array $values,
        ?callable    $conditionClb = null,
        ?string      $conditionMsg = null,
        ?callable    $parseClb = null,
    ): void
    {
        foreach ((array) $values as $value) {
            $value = isset($parseClb) ? $parseClb($value) : $value;

            if (isset($conditionClb) && $conditionClb($value)) {
                throw new \InvalidArgumentException(sprintf($conditionMsg, $value));
            }

            $this->$paramName[] = $value;
        }
    }

    /**
     * @param string|string[] $dirs
     */
    public function in(string|array $dirs): static
    {
        $this->fillParameter(
            "dirs", $dirs,
            fn($dirName) => !is_dir($dirName), 'Directory with path %s does not exist',
            fn($dirName) => rtrim($dirName, DIRECTORY_SEPARATOR)
        );

        return $this;
    }

    /**
     * @param string|string[] $patterns
     */
    public function path(string|array $patterns): static
    {
        $this->fillParameter("paths", $patterns);
        return $this;
    }

    /**
     * @param string|string[] $patterns
     */
    public function notPath(string|array $patterns): static
    {
        $this->fillParameter("notPaths", $patterns);
        return $this;
    }

    /**
     * @param string|string[] $patterns
     */
    public function name(string|array $patterns): static
    {
        $this->fillParameter("names", $patterns);
        return $this;
    }

    /**
     * @param string|string[] $patterns
     */
    public function notName(string|array $patterns): static
    {
        $this->fillParameter("notNames", $patterns);
        return $this;
    }

    protected function getDirectoryIterator(string $dir): \Iterator
    {
        $iterator = new RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new FileTypeFilterIterator($iterator, $this->mode);
        $iterator = new PathFilterIterator($iterator, $this->paths, $this->notPaths);
        $iterator = new NameFilterIterator($iterator, $this->names, $this->notNames);

        return $iterator;
    }

    public function getIterator(): \Traversable
    {
        $iterator = new \AppendIterator();

        foreach ($this->dirs as $dir) {
            $iterator->append($this->getDirectoryIterator($dir));
        }

        return $iterator;
    }
}