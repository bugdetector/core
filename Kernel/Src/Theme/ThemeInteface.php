<?php

namespace Src\Theme;

use CoreDB\Kernel\ControllerInterface;

interface ThemeInteface
{
    /**
     * Process page, for example use argument. Do your own operation
     * Or define theme page build actions and fields.
     */
    public function render(ControllerInterface $controller);

    public static function getTemplateDirectories(): array;
}
