<?php

namespace App\Core\DependencyInjection\ParamContainer;

use App\Core\DependencyInjection\ParamContainer\Exceptions\NotFoundParamException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class ParamContainer implements ContainerInterface
{
    public const string DEFAULT_KEY_SEPARATOR = '.';

    public function __construct(
        protected array $params = [],
        protected string $keySeparator = self::DEFAULT_KEY_SEPARATOR
    )
    {
    }

    public function get(string $id): mixed
    {
        $keyParts = explode($this->keySeparator, $id);
        $result = $this->params;

        while (($keyPart = array_shift($keyParts)) !== null) {
            if (isset($result[$keyPart])) {
                $result = $result[$keyPart];
                continue;
            }

            throw new NotFoundParamException("Key '{$id}' not found");
        }

        return $result;
    }

    public function has(string $id): bool
    {
        try {
            $this->get($id);
        } catch (ContainerExceptionInterface) {
            return false;
        }

        return true;
    }
}