<?php

namespace Src\Controller;

use Src\Entity\Translation;
use Src\Theme\BaseTheme\BaseTheme;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class MainpageController extends BaseTheme
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
        return Translation::getTranslation("mainpage_welcome_message");
    }
}
