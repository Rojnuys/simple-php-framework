<?php

declare(strict_types=1);

namespace App\Shortener\Interfaces;

interface ICodeGenerator
{
    public function generate(int $length): string;
}