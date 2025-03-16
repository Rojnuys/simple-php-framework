<?php

namespace App\Core\Config\Parsers;

use App\Core\Config\Exceptions\ConfigParseException;
use App\Core\Config\Interfaces\IConfigParser;

class PhpConfigParser implements IConfigParser
{
    /**
     * @throws ConfigParseException
     */
    public function parse(string $filePath): array
    {
        try {
            $configs = include $filePath;
            if (!is_array($configs)) {
                throw new ConfigParseException('PHP file must return an array');
            }
            return $configs;
        } catch (\Throwable) {
            throw new ConfigParseException('PHP parse error');
        }
    }

    public function canParse(string $fileExtension): bool
    {
        return $fileExtension === "php";
    }
}