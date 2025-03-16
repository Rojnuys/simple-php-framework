<?php

namespace App\Core\DependencyInjection\Container\Interfaces;

interface IBuilder
{
    public function build(): object;
}