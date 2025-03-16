<?php

namespace App\Core\Core\Events;

class ExceptionEvent
{
    public function __construct(protected \Throwable $exception)
    {
    }

    public function getException(): \Throwable {
        return $this->exception;
    }
}