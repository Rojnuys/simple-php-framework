<?php

use App\Core\Config\Enums\ConfigTypeKeys;

return [
    ConfigTypeKeys::PARAMETERS => [
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
];