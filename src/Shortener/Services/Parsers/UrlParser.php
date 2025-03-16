<?php

declare(strict_types=1);

namespace App\Shortener\Services\Parsers;

use App\Shortener\Interfaces\IUrlParser;

class UrlParser implements IUrlParser
{
    public function parse(string $url): string
    {
        return rtrim($url, '/');
    }
}
