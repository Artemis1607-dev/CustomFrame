<?php

/**
 * Integrates dependencies and boots the application.
 */

use Core\Kernel;

$config = [
    'dotenv' => [
        'relative_path' => __DIR__ . '/../',
    ],
    'modelwrapper' => [
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
    ],
    'bladeone' => [
        'views_path' => __DIR__ . '/../resources/views/',
        'cache_path' => __DIR__ . '/../storage/views/',
    ],
    'application' => [
        'production' => true,
    ],
];

if (php_sapi_name() !== 'cli') {
    // Boot the application
    $kernel = new Kernel($config);
    $kernel->bootApplication();
}