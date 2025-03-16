<?php

namespace App\Core\Config\Parsers;

use App\Core\Config\Exceptions\ConfigParseException;
use App\Core\Config\Interfaces\IConfigParser;

class ConfigParser implements IConfigParser
{
    /**
     * @param IConfigParser[] $parsers
     */
    protected array $parsers = [];

    public function __construct(array $parsers = [])
    {
        foreach ($parsers as $parser) {
            $this->addParser($parser);
        }
    }

    protected function addParser(IConfigParser $parser): void
    {
        $this->parsers[] = $parser;
    }

    /**
     * @throws ConfigParseException
     */
    public function parse(string $filePath): array
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        foreach ($this->parsers as $parser) {
            if ($parser->canParse($fileExtension)) {
                return $parser->parse($filePath);
            }
        }

        throw new ConfigParseException("No parser found for {$fileExtension} file extension");
    }

    public function canParse(string $fileExtension): bool
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canParse($fileExtension)) {
                return true;
            }
        }

        return false;
    }
}