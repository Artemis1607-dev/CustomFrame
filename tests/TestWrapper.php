<?php

use TestCase;

/** Provides various helper methods and properties to descendent tests. */
class TestWrapper extends TestCase
{
    protected array $config = [
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
            'production' => true,
            'routes' => [
                Route::get('/', [Controller::class => 'foo']),
                Route::patch('/json', [Controller::class => 'foo']),
            ],
        ]
    ];

    protected function simulateRequest(
        string $method = 'GET',
        string $url = '/',
        string|array $body = ['foo' => 'bar'],
        array $headers = ['HTTP_ACCEPT' => 'text/css']
    ): void {
        if (is_array($body)) {
            $_GET = $body;
        }
        $_SERVER = $headers;
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['SERVER_NAME'] = 'www.foo.com';
    }
}