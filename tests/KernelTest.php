<?php

use Core\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * Testes the following conditions:
 * 
 * * Valid, Invalid, Empty config
 */
final class KernelTest extends TestCase
{
    public static $config = [
        'env' => [
            'relative_path' => __DIR__ . '/../'
        ],
        'database' => [
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => ''
        ],
        'compiler' => [
            'views_path' => [
                __DIR__ . '/../resources/views/',
                __DIR__ . '/../resources/views/components',
                __DIR__ . '/../resources/views/exceptions',
                __DIR__ . '/../resources/views/templates',
            ],
            'cache_path' => __DIR__ . '/../storage/views/'
        ],
        'app' => [
            'production' => 'true',
            'routes' => []
        ]
    ];

    public function testFailOnInvalidConfig(): void
    {
        // Expect
        $this->expectException(\LogicException::class);
        // Act
        new Kernel(['app', 'env']);
    }

    public function testFailOnEmptyConfig(): void
    {
        // Expect
        $this->expectException(\LogicException::class);
        // Act
        new Kernel([]);
    }

    public static function loadDependencies()
    {
        new Kernel(self::$config)->loadDependencies();
    }
}