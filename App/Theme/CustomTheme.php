<?php

namespace App\Theme;

use Src\BaseTheme\BaseTheme;

class CustomTheme extends BaseTheme{

    public static function getTemplateDirectories(): array
    {
        $directories = parent::getTemplateDirectories();
        array_unshift($directories, __DIR__."/templates");
        return $directories;
    }

}