<?php

namespace App\Core\Config\Interfaces;

use App\Core\Config\Exceptions\ConfigStructureException;

interface IConfigStructureValidator
{
    /**
     * @throws ConfigStructureException
     */
    public function validate(array $configs): void;
}