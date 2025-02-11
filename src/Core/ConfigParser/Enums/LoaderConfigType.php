<?php

namespace App\Core\ConfigParser\Enums;

enum LoaderConfigType: string
{
    public const string RESOURCE = 'resource';
    public const string EXCLUDE = 'exclude';
    public const string AUTOWIRE = 'autowire';
    public const string AUTOCONFIGURE = 'autoconfigure';

    public static function getAllTypes(): array
    {
        return [
            LoaderConfigType::RESOURCE,
            LoaderConfigType::EXCLUDE,
            LoaderConfigType::AUTOWIRE,
            LoaderConfigType::AUTOCONFIGURE
        ];
    }
}
