<?php

namespace App\Core\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected string $scheme = '';
    protected string $host = '';
    protected ?int $port = null;
    protected string $userInfo = '';
    protected string $query = '';
    protected string $path = '';
    protected string $fragment = '';

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $this->parseUri($uri);
        }
    }

    protected function parseUri(string $uri): void
    {
        $parts = parse_url($uri);

        if ($parts === false) {
            throw new \InvalidArgumentException("Invalid URI '{$uri}'");
        }

        $this->scheme = $parts['scheme'] ?? '';
        $this->host = $parts['host'] ?? '';
        $this->port = $parts['port'] ?? null;
        $this->userInfo = $parts['user'] ?? '';
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
        $this->query = $parts['query'] ?? '';
        $this->path = $parts['path'] ?? '';
        $this->fragment = $parts['fragment'] ?? '';
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        $authority = $this->host;

        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z\d+\-.]*$/', $scheme)) {
            throw new \InvalidArgumentException("Invalid URI scheme '{$scheme}'");
        }

        $clonedUri = clone $this;
        $clonedUri->scheme = strtolower($scheme);
        return $clonedUri;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $clonedUri = clone $this;
        $clonedUri->userInfo = $user;

        if ($password !== null) {
            $clonedUri->userInfo .= ':' . $password;
        }

        return $clonedUri;
    }

    public function withHost(string $host): UriInterface
    {
        $clonedUri = clone $this;
        $clonedUri->host = strtolower($host);
        return $clonedUri;
    }

    public function withPort(?int $port): UriInterface
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new \InvalidArgumentException("Invalid port '{$port}'");
        }

        $clonedUri = clone $this;
        $clonedUri->port = $port;
        return $clonedUri;
    }

    public function withPath(string $path): UriInterface
    {
        $clonedUri = clone $this;
        $clonedUri->path = $path;
        return $clonedUri;
    }

    public function withQuery(string $query): UriInterface
    {
        $clonedUri = clone $this;
        $clonedUri->query = ltrim($query, '?');
        return $clonedUri;
    }

    public function withFragment(string $fragment): UriInterface
    {
        $clonedUri = clone $this;
        $clonedUri->fragment = ltrim($fragment, '#');
        return $clonedUri;
    }

    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . '://';
        }

        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= $authority;
        }

        if ($this->path !== '') {
            if ($this->path[0] !== '/') {
                $uri .= '/';
            }
            $uri .= $this->path;
        }

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}