<?php

use Core\Response;
use PHPUnit\Framework\TestCase;

/**
 * Testing the following conditions:
 * 
 * * Invalid {construct, view, file, json} redirect range
 * * Invalid {file, json} body
 */
class ResponseTest extends TestCase
{
    public function testFailOnInvalidConstructStatusCodeRange()
    {
        $this->expectException(\OutOfRangeException::class);

        new Response('foo', 50);
    }

    public function testFailOnInvalidViewStatusCodeRange()
    {
        $this->expectException(\OutOfRangeException::class);

        Response::prepareView('foo', [], 300);
    }

    public function testFailOnInvalidJsonStatusCodeRange()
    {
        $this->expectException(\OutOfRangeException::class);

        $valid_json = json_decode('{"foo": "bar"}', true);

        Response::prepareJson($valid_json, 512, 300);
    }

    public function testFailOnInvalidJsonArrayDepth()
    {
        $this->expectException(\JsonException::class);

        $invalid_json = [
            1 => [
                2 => [
                    3 => 'foo'
                ]
            ]
        ];

        Response::prepareJson($invalid_json, 2);
    }

    public function testFailOnInvalidFileStatusCodeRange()
    {
        $this->expectException(\OutOfRangeException::class);

        Response::prepareFile(__DIR__ . '../resources/views/foo.blade.php', '650');
    }

    public function testFailOnInvalidFilePath()
    {
        $this->expectException(\LogicException::class);

        Response::prepareFile(__DIR__ . '../resources/views/foo.blade');
    }

    public function testFailOnInvalidRedirectStatusCodeRange()
    {
        $this->expectException(\OutOfRangeException::class);

        Response::prepareRedirect('/foo', 299);
    }
} 