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
    /**
     * Renders a blade view.
     * 
     * @param string $view
     *        Accepts a relative path to a blade file.
     * @param array $data
     *        Accepts an associative array with the blade
     *        parameters to integrate into the view.
     * @throws \LogicException
     */
    public static function render(string $view, array $data = []): string
    {
        if (!isset(BladeOne::$instance)) {
            throw new \LogicException('Bladeone instance not found', 500);
        }
        return BladeOne::$instance->run($view, $data);
    }
}