<?php

namespace App\Core\DependencyInjection\ServiceConfigLoader\Exceptions;

use App\Core\DependencyInjection\ServiceConfigLoader\Interfaces\IServiceConfigLoaderException;

class ServiceConfigLoaderException extends \InvalidArgumentException implements IServiceConfigLoaderException
{
}