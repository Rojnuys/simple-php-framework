<?php

declare(strict_types=1);

namespace App\Shortener\Services\Validators;

use App\Shortener\Enums\UrlAvailableHttpStatus;
use App\Shortener\Interfaces\IUrlValidator;

class UrlValidator implements IUrlValidator
{
    /**
     * @throws \InvalidArgumentException
     */
    public function checkFormat(string $url): true
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL format.");
        }

        return true;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkAvailability(string $url): true
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $httpStatus = UrlAvailableHttpStatus::tryFrom($httpCode);

        if (is_null($httpStatus)) {
            throw new \InvalidArgumentException('The Url is not available.');
        }

        return true;
    }
}