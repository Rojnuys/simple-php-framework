<?php

declare(strict_types=1);

namespace App\Shortener\Interfaces;

interface IUrlParser
{
    public function parse(string $url): string;
}