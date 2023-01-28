<?php

namespace App\Theme;

use CoreDB\Kernel\BaseController;
use Src\Theme\ThemeInteface;

abstract class AppController extends BaseController
{
    public function getTheme(): ThemeInteface
    {
        return new AppTheme();
    }
}
