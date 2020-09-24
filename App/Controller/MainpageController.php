<?php

namespace Src\Controller;

use App\Theme\CustomTheme;
use Src\Entity\Translation;

class MainpageController extends CustomTheme
{

    public $content;
    public function checkAccess(): bool
    {
        return true;
    }

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("welcome"));
        
    }
    public function echoContent()
    {
        return "Hello";
    }
}
