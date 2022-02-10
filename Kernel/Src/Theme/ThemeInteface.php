<?php

namespace Src\Theme;

use CoreDB\Kernel\ControllerInterface;

interface ThemeInteface
{
    /**
     * Set default theme values before preprocess.
     */
    public function setDefaults(ControllerInterface $controller);
    /**
     * Simply render page using controller.
     */
    public function render(ControllerInterface $controller);

    public static function getTemplateDirectories(): array;
}
