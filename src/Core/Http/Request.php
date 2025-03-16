<?php

namespace App\Core\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    protected string $method;
    protected string $requestTarget;
    protected UriInterface $uri;
    protected array $headers = [];
    protected StreamInterface $body;
    protected string $protocolVersion;

    public function __construct(
        string $method,
        UriInterface $uri,
        array $headers = [],
        ?StreamInterface $body = null,
        string $protocolVersion = '1.1'
    ) {
        parent::__construct($headers, $body, $protocolVersion);

        $this->method = $method;
        $this->uri = $uri;
        $this->requestTarget = $uri->getPath() . ($uri->getQuery() ? '?' . $uri->getQuery() : '');
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    public function withRequestTarget(string $requestTarget): static
    {
        $clonedRequest = clone $this;
        $clonedRequest->requestTarget = $requestTarget;
        return $clonedRequest;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): static
    {
        $clonedRequest = clone $this;
        $clonedRequest->method = $method;
        return $clonedRequest;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $clonedRequest = clone $this;
        $clonedRequest->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('host')) {
            $clonedRequest->headers['host'] = [$uri->getHost()];
        }

        return $clonedRequest;
    }
}
