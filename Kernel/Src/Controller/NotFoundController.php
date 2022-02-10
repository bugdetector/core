<?php

namespace Src\Controller;

use App\Theme\CustomTheme;
use CoreDB\Kernel\BaseController;
use Src\Entity\Translation;
use Src\Theme\ThemeInteface;

class NotFoundController extends BaseController
{

    public $error_code = 404;
    public $message;

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
        $this->message = Translation::getTranslation("page_not_found");
        $this->setTitle(Translation::getTranslation("sorry") . ": " . Translation::getTranslation("page_not_found"));
        http_response_code($this->error_code);
    }
    
    public function getTemplateFile(): string
    {
        return "page-error.twig";
    }
}
