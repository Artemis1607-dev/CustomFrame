<?php

namespace Core;

use eftec\bladeone\BladeOne;

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