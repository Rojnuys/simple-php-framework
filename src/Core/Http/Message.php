<?php

namespace App\Core\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    protected array $headers;
    protected StreamInterface $body;
    protected string $protocolVersion;
    
    public function __construct(array $headers = [], ?StreamInterface $body = null, string $protocolVersion = '1.1')
    {
        $this->headers = $this->normalizeHeaders($headers);
        $this->body = $body ?? new Stream(fopen('php://temp', 'r+'));
        $this->protocolVersion = $protocolVersion;
    }

    protected function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $values) {
            $normalized[strtolower($name)] = (array) $values;
        }

        return $normalized;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $clonedMessage = clone $this;
        $clonedMessage->protocolVersion = $version;
        return $clonedMessage;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->headers);
    }

    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @param string|string[] $value
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $clonedMessage = clone $this;
        $clonedMessage->headers[strtolower($name)] = (array) $value;
        return $clonedMessage;
    }

    /**
     * @param string|string[] $value
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $clonedMessage = clone $this;
        $clonedMessage->headers[strtolower($name)] = array_merge(
            $this->getHeader($name),
            (array) $value
        );

        return $clonedMessage;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $clonedMessage = clone $this;
        unset($clonedMessage->headers[strtolower($name)]);
        return $clonedMessage;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $clonedMessage = clone $this;
        $clonedMessage->body = $body;
        return $clonedMessage;
    }
}