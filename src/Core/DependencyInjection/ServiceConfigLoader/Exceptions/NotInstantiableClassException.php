<?php

namespace App\Core\DependencyInjection\ServiceConfigLoader\Exceptions;

use App\Core\DependencyInjection\ServiceConfigLoader\Interfaces\IServiceConfigLoaderException;

class NotInstantiableClassException extends \Exception implements IServiceConfigLoaderException
{
}