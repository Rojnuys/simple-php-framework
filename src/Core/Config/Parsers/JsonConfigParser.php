<?php

namespace App\Core\Config\Parsers;

use App\Core\Config\Exceptions\ConfigParseException;
use App\Core\Config\Interfaces\IConfigParser;

class JsonConfigParser implements IConfigParser
{
    /**
     * @throws ConfigParseException
     */
    public function parse(string $filePath): array
    {
        try {
            $configs = json_decode(file_get_contents($filePath), true);
            if (!is_array($configs)) {
                throw new ConfigParseException('JSON file must return an array');
            }
            return $configs;
        } catch (\Throwable) {
            throw new ConfigParseException('JSON parse error');
        }
    }

    public function canParse(string $fileExtension): bool
    {
        return $fileExtension === "json";
    }
}