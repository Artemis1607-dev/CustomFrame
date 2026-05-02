<?php

/**
 * Loads configuration and boots the application.
 * 
 * This file serves as a bridge between the public and the private
 * server internals, keeping everything secure. Note that it is
 * possible and intended to extend the global configuration to
 * separate files, providing a great organisation in terms of
 * dependency and application configuration.
 * 
 * @see Core\Kernel
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

new Kernel($config)->handleRequest();