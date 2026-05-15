<?php

use Core\Route;
use PHPUnit\Framework\TestCase;

/**
 * Testing the following conditions:
 * 
 * * Existing config/routes.php
 * * Existing class and method
 */
class Helpers extends TestCase
{
    public function testPursueOnExistingRoutes()
    {
        $routes = routes();

        $this->assertContainsOnlyInstancesOf(Route::class, $routes);
    }

    public function testPursueOnExistingClassAndMethod()
    {
        $assert = validate(Route::class, 'get');

        $this->assertTrue($assert);
    }
}