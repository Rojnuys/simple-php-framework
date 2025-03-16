<?php

namespace App\Core\Http;

class HttpRequest extends Request
{
    public function __construct(
        protected array $get = [],
        protected array $post = [],
        protected array $server = [],
    )
    {
        $uri = new Uri($server['REQUEST_URI']);
        $headers = getallheaders();
        $body = new Stream(fopen('php://input', 'r'));
        $protocolVersion = substr($server['SERVER_PROTOCOL'], strpos($server['SERVER_PROTOCOL'], '/'));

        parent::__construct($server['REQUEST_METHOD'], $uri, $headers, $body, $protocolVersion);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->get[$name] ?? $default;
    }

    public function post(string $name, mixed $default = null): mixed
    {
        return $this->post[$name] ?? $default;
    }
}