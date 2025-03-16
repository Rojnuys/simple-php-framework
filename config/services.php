<?php

use App\Core\Config\Enums\ConfigTypeKeys;
use App\Core\Config\Enums\ServiceConfigTypeKeys;
use App\Shared\FileSystem\File\FileReader;
use App\Shared\FileSystem\File\FileWriter;
use App\Shared\FileSystem\File\Interfaces\IFileReader;
use App\Shared\FileSystem\File\Interfaces\IFileWriter;

return [

    ConfigTypeKeys::PARAMETERS => [
        'shortener' => [
            'code_length' => 4,
            'storage_path' => '%core.cache_dir%/url_code_pair.txt'
        ],
    ],

    ConfigTypeKeys::SERVICES => [
        '_default' => [
            ServiceConfigTypeKeys::AUTO_INJECTING => true,
            ServiceConfigTypeKeys::AUTO_TAGGING => true,
        ],

        'App\\' => [
            ServiceConfigTypeKeys::RESOURCE => '',
            ServiceConfigTypeKeys::EXCLUDE => ['{Core,Shared,Views}/**', '**{Exceptions,DTO,Entities,Events}**'],
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