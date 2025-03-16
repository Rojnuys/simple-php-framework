<?php

declare(strict_types=1);

namespace App\Shortener\Enums;

enum UrlAvailableHttpStatus : int
{
    case OK = 200;
    case CREATED = 201;
    case MOVED_PERMANENTLY = 301;
    case FOUND = 302;
}