<?php

declare(strict_types=1);

namespace App\Shortener\DTO;

readonly class UrlCodePairCreateDTO
{
    public function __construct(public string $url, public string $code)
    {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['url']) || !isset($data['code'])) {
            throw new \InvalidArgumentException("The parameters 'url' and 'code' must be set");
        }

        return new self($data['url'], $data['code']);
    }
}