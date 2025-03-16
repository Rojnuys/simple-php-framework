<?php

namespace App\Core\Core\Events;

use App\Core\EventDispatcher\Event;
use Psr\Http\Message\ResponseInterface;

class ResponseEvent extends Event
{
    public function __construct(protected ResponseInterface $response)
    {
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}