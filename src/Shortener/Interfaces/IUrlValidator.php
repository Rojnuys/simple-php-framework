<?php

declare(strict_types=1);

namespace App\Shortener\Interfaces;

interface IUrlValidator
{
    /**
     * @throws \InvalidArgumentException
     */
    public function checkFormat(string $url): bool;

    /**
     * @throws \InvalidArgumentException
     */
    public function checkAvailability(string $url): bool;
}