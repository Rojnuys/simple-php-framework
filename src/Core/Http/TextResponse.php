<?php

namespace App\Core\Http;

class TextResponse extends Response
{
    public function __construct(string $body, int $statusCode = 200)
    {
        parent::__construct(statusCode: $statusCode, headers: ['Content-Type' => 'text/plain']);
        $this->body->write($body);
    }
}