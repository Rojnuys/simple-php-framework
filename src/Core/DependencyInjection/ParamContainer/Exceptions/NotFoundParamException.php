<?php

namespace App\Core\DependencyInjection\ParamContainer\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundParamException extends \Exception implements NotFoundExceptionInterface
{
}