<?php

use App\Core\Config\Enums\ConfigTypeKeys;
use App\Core\Config\Enums\ServiceConfigTypeKeys;
use App\Shared\FileSystem\File\FileReader;
use App\Shared\FileSystem\File\FileWriter;
use App\Shared\FileSystem\File\Interfaces\IFileReader;
use App\Shared\FileSystem\File\Interfaces\IFileWriter;
use App\Shortener\Interfaces\IUrlCodePairRepository;
use App\Shortener\Repositories\FileUrlCodePairRepository;

return [
    ConfigTypeKeys::PARAMETERS => [
        'shortener' => [
            'code_length' => 4,
            'storage_path' => '%core.cache_dir%/url_code_pair.txt'
        ],
        'db' => [
            'driver' => getenv('DB_DRIVER') ?: 'mysql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'database' => getenv('DB_DATABASE') ?: getenv('PROJECT_NAME') ?: 'database',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => getenv('DB_CHARSET') ?: 'utf8',
            'collation' => getenv('DB_COLLATION') ?: 'utf8_unicode_ci',
            'prefix' => getenv('DB_PREFIX') ?: '',
        ],
    ],

    ConfigTypeKeys::SERVICES => [
        '_default' => [
            ServiceConfigTypeKeys::AUTO_INJECTING => true,
            ServiceConfigTypeKeys::AUTO_TAGGING => true,
        ],

        'App\\' => [
            ServiceConfigTypeKeys::RESOURCE => '',
            ServiceConfigTypeKeys::EXCLUDE => ['{Core,Shared,Views}/**', '**{Exceptions,DTO,Entities,Events,Models}**'],
        ],

        IUrlCodePairRepository::class => [
            ServiceConfigTypeKeys::CLASSNAME => FileUrlCodePairRepository::class,
        ],

        IFileWriter::class => [
            ServiceConfigTypeKeys::CLASSNAME => FileWriter::class,
            ServiceConfigTypeKeys::ARGS => [
                'path' => '%shortener.storage_path%'
            ],
        ],

        IFileReader::class => [
            ServiceConfigTypeKeys::CLASSNAME => FileReader::class,
            ServiceConfigTypeKeys::ARGS => [
                'path' => '%shortener.storage_path%'
            ],
        ],
    ],
];