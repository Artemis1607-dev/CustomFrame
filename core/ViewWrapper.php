<?php

namespace Core;

use eftec\bladeone\BladeOne;

/**
 * Provides a compiler instance.
 * 
 * The purpose of ViewWrapper is to wrap a compiler instance so that
 * it can be accessed from the codebase.
 */
class ViewWrapper
{
    public static function render(string $view, array $data = []): string
    {
        if (!isset(BladeOne::$instance)) {
            throw new \LogicException('Bladeone instance not found', 500);
        }
        return BladeOne::$instance->run($view, $data);
    }
}