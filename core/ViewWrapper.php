<?php

namespace Core;

use eftec\bladeone\BladeOne;

class ViewWrapper
{
    public static function render(string $view, array $data = []) {
        return BladeOne::$instance->run($view, $data);
    }
}