<?php

namespace App\Core\Framework\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    /**
     * @var string[]
     */
    protected array $methods;

    /**
     * @param string[] $methods
     */
    public function __construct(protected string $path, array $methods = ['GET', 'POST', 'PUT', 'DELETE'])
    {
        foreach ($methods as &$method) {
            $method = strtoupper($method);
        }

        $this->methods = $methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}