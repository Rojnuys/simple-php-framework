<?php

declare(strict_types=1);

namespace App\Shortener\Exceptions;

class UrlCodePairDoesNotExistException extends \Exception
{
    public function __construct(
        string $message = "The UrlCodePair does not exist.",
        int    $code = 0, ?\Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}