<?php

namespace App\Exceptions;

class SessionException extends \RuntimeException
{
    public function __construct(string $message, int $code = 403)
    {
        parent::__construct($message, $code);
    }
}