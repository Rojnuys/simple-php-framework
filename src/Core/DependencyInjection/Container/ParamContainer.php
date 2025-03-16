<?php

namespace App\Core\DependencyInjection\Container;

use App\Core\DependencyInjection\Container\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ParamContainer implements ContainerInterface
{
    public const string DEFAULT_ID_SEPARATOR = '.';

    public function __construct(
        protected array $parameters = [],
        protected string $idSeparator = self::DEFAULT_ID_SEPARATOR
    )
    {
    }

    protected function getIdParts(string $id): array
    {
        return explode($this->idSeparator, $id);
    }

    /**
     * @throws NotFoundExceptionInterface
     */
    public function get(string $id): mixed
    {
        $idParts = $this->getIdParts($id);
        $parameter = $this->parameters;

        while (($idPart = array_shift($idParts)) !== null) {
            if (isset($parameter[$idPart])) {
                $parameter = $parameter[$idPart];
                continue;
            }

            throw new NotFoundException("Parameter '{$id}' not found");
        }

        return $parameter;
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