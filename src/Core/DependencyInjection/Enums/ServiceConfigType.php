<?php

namespace App\Core\DependencyInjection\Enums;

enum ServiceConfigType: string
{
    public const string CLASSNAME = 'class';
    public const string ARGS = 'arguments';
    public const string TAGS = 'tags';
    public const string CALLS = 'calls';
    public const string SHARED = 'shared';
}
