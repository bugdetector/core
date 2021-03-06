<?php

namespace App\Controller;

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
        $titleSuffix = \CoreDB::currentUser()->isLoggedIn() ? ", " . \CoreDB::currentUser()->name : "";
        $this->setTitle(Translation::getTranslation("welcome") . $titleSuffix);
    }
    public function echoContent()
    {
        return Translation::getTranslation("mainpage_welcome_message");
    }
}
