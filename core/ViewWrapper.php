<?php

namespace Core;

use eftec\bladeone\BladeOne;

class ViewWrapper
{
    public static function render(string $view, array $data = []): string
    {
        if (!isset(BladeOne::$instance)) {
            throw new \LogicException('Failed to get the last compiler instance');
        }
        return BladeOne::$instance->run($view, $data);
    }
}