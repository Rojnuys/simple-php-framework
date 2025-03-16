<?php

namespace App\Core\Config\Interfaces;

use App\Core\Config\Exceptions\ConfigParseException;

interface IConfigParser
{
    /**
     * @throws ConfigParseException
     */
    public function parse(string $filePath): array;
    public function canParse(string $fileExtension): bool;
}