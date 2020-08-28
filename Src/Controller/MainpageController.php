<?php
namespace Src\Controller;

use Src\Theme\BaseTheme\BaseTheme;

class MainpageController extends BaseTheme
{

    public function checkAccess() : bool
    {
        return true;
    }

    public function preprocessPage()
    {
    }
    public function echoContent()
    {
    }
}
