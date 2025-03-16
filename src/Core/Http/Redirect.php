<?php

namespace App\Core\Http;

class Redirect extends Response
{
    public function __construct(string $url, int $statusCode = 302)
    {
        parent::__construct(statusCode: $statusCode, headers: ['Location' => $url]);
    }
}