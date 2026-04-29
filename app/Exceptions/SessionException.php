<?php

namespace App\Exceptions;

class SessionException extends \RuntimeException
{
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }
}