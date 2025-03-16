<?php

declare(strict_types=1);

namespace App\Shortener\Entities;

class UrlCodePair
{
    public function __construct(
        protected string $id,
        protected string $url,
        protected string $code,
        protected int $count = 0
    )
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function increaseCount(): void
    {
        $this->count++;
    }
}