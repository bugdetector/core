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
        \CoreDB::HTMLMail(
            "bakiyucel38@gmail.com",
            "deneme",
            "deneme mail",
            "Murat Baki Yücel"
        );
        $this->setTitle(Translation::getTranslation("welcome"));
        
    }
    public function echoContent()
    {
        return Translation::getTranslation("mainpage_welcome_message");
    }
}
