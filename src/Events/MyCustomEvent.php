<?php

namespace App\Events;

use App\Core\EventDispatcher\Event;

class MyCustomEvent extends Event
{
    protected int $result = 0;

    public function __construct(protected int $a = 5, protected int $b = 6)
    {
    }

    public function getA(): int
    {
        return $this->a;
    }

    public function getB(): int
    {
        return $this->b;
    }

    public function getResult(): int
    {
        return $this->result;
    }

    public function setResult(int $result): void
    {
        $this->result = $result;
    }
}