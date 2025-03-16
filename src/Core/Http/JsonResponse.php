<?php

namespace App\Core\Http;

class JsonResponse extends Response
{
    public function __construct(mixed $body, int $statusCode = 200)
    {
        parent::__construct(statusCode: $statusCode, headers: ['Content-Type' => 'application/json']);
        $this->body->write(json_encode($body));
    }
}