<?php

namespace App\Core\DependencyInjection\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
}