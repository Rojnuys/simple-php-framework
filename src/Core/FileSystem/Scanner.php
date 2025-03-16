<?php

namespace App\Core\FileSystem;

class Scanner implements \IteratorAggregate
{
    /**
     * @var string[]
     */
    protected array $directories = [];

    /**
     * @param string[] $directories
     */
    public function __construct(string|array $directories = [], protected $recursive = true)
    {
        $this->addDirectories($directories);
    }

    public function addDirectories(string|array $directories): static
    {
        foreach ((array) $directories as $directory) {
            if (!is_dir($directory)) {
                throw new \InvalidArgumentException("Directory '{$directory}' does not exist");
            }

            $this->directories[] = rtrim($directory, DIRECTORY_SEPARATOR);
        }

        return $this;
    }

    public function getIterator(): \Iterator
    {
        $iterator = new \AppendIterator();

        foreach ($this->directories as $directory) {
            if ($this->recursive) {
                $iterator->append(new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                ));
            } else {
                $iterator->append(new \DirectoryIterator($directory));
            }
        }

        return $iterator;
    }
}