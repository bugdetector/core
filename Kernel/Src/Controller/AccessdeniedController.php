<?php

namespace Src\Controller;

use Src\Entity\Translation;
use Src\BaseTheme\BaseTheme;

class AccessdeniedController extends BaseTheme
{

    public $error_code = 403;
    public $message;
    
    public function checkAccess(): bool
    {
        return true;
    }
    
    public function preprocessPage()
    {
        $this->message = Translation::getTranslation("access_denied");
        $this->setTitle(Translation::getTranslation("sorry") . ": " . Translation::getTranslation("access_denied"));
        http_response_code($this->error_code);
    }
    
    public function getTemplateFile(): string
    {
        return "page-error.twig";
    }
}
