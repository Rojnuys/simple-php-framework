<?php

namespace App\Core\Http;

class ViewResponse extends Response
{
    public function __construct(string $path, array $parameters = [], int $statusCode = 200)
    {
        parent::__construct(statusCode: $statusCode, headers: ['Content-Type' => 'text/html']);
        extract($parameters);
        ob_start();
        try {
            require __DIR__ . '/../../Views/' . $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        $this->body->write(ob_get_clean());
    }
}