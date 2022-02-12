<?php

namespace App\Controller;

use App\Theme\CustomTheme;
use CoreDB\Kernel\BaseController;
use Src\Entity\Translation;
use Src\Theme\ThemeInteface;

class MainpageController extends BaseController
{

    public $content;

    public function getTheme(): ThemeInteface
    {
        return new CustomTheme();
    }

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
