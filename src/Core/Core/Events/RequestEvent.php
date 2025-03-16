<?php

namespace App\Core\Core\Events;

use App\Core\EventDispatcher\Event;
use App\Core\Http\HttpRequest;

class RequestEvent extends Event
{
    public function __construct(protected HttpRequest $request)
    {
    }

    public function getRequest(): HttpRequest
    {
        return $this->request;
    }

    public function setRequest(HttpRequest $request): void
    {
        $this->request = $request;
    }
}