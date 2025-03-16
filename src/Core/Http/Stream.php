<?php

namespace App\Core\Http;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var resource|null
     */
    protected $resource = null;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('Stream must be initialized with a valid resource');
        }

        $this->resource = $resource;
    }

    public function __toString(): string
    {
        if ($this->resource !== null) {
            try {
                $this->rewind();
                return stream_get_contents($this->resource);
            } catch (\Throwable) {
            }
        }

        return '';
    }

    public function close(): void
    {
        if ($this->resource !== null) {
            fclose($this->resource);
            $this->resource = null;
        }
    }

    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function getSize(): ?int
    {
        if ($this->resource === null) {
            return null;
        }

        $stats = fstat($this->resource);
        return $stats['size'] ?? null;
    }

    public function tell(): int
    {
        if ($this->resource === null) {
            throw new \RuntimeException('Stream is not open');
        }

        $position = ftell($this->resource);

        if ($position === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $position;
    }

    public function eof(): bool
    {
        if ($this->resource === null) {
            throw new \RuntimeException('Stream is not open');
        }

        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $metadata = $this->getMetadata();
        return $metadata['seekable'] ?? false;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek in stream');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $metadata = $this->getMetadata();
        return str_contains($metadata['mode'], 'w') || str_contains($metadata['mode'], '+');
    }

    public function write(string $string): int
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    public function isReadable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $metadata = $this->getMetadata();
        return str_contains($metadata['mode'], 'r') || str_contains($metadata['mode'], '+');
    }

    public function read(int $length): string
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $result;
    }

    public function getContents(): string
    {
        if ($this->resource === null) {
            throw new \RuntimeException('Stream is not open');
        }

        $contents = stream_get_contents($this->resource);

        if ($contents === false) {
            throw new \RuntimeException('Unable to get stream contents');
        }

        return $contents;
    }

    public function getMetadata(?string $key = null): mixed
    {
        if ($this->resource === null) {
            return $key === null ? [] : null;
        }

        $metadata = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }
}