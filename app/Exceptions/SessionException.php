<?php

namespace App\Exceptions;

/** Provides an Exception constructor for concerned middlewares. */
class SessionException extends \RuntimeException
{
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }
}