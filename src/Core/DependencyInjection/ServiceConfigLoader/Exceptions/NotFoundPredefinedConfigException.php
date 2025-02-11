<?php

namespace App\Core\DependencyInjection\ServiceConfigLoader\Exceptions;

use App\Core\DependencyInjection\ServiceConfigLoader\Interfaces\IServiceConfigLoaderException;

class NotFoundPredefinedConfigException extends \Exception implements IServiceConfigLoaderException
{
}