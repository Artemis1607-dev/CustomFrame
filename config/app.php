<?php

/**
 * Integrates dependencies and boots the application.
 */

use Core\Kernel;

$config = [
    'dotenv' => [
        'relative_path' => __DIR__ . '/../'
    ],
    'modelwrapper' => [
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => ''
    ],
    'bladeone' => [
        'views_path' => [
            __DIR__ . '/../resources/views/',
            __DIR__ . '/../resources/views/components',
            __DIR__ . '/../resources/views/exceptions',
            __DIR__ . '/../resources/views/templates',
        ],
        'cache_path' => __DIR__ . '/../storage/views/'
    ],
    'application' => [
        'production' => true
    ]
];

// Boot the application
new Kernel($config)->handleRequest();