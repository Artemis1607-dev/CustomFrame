<?php

use Core\ModelWrapper;
use PHPUnit\Framework\TestCase;

/**
 * Testing the following condition:
 * 
 * * Invalid database credentials
 */
class ModelTest extends TestCase
{
    public function testFailOnInvalidDatabaseCredentials()
    {
        $this->expectException(\Exception::class);

        new ModelWrapper('0.0.0.0', 'foo', 'bar', 'baz');
    }
}