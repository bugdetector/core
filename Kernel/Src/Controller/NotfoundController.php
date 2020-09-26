<?php

namespace Src\Controller;

use Src\Entity\Translation;
use Src\BaseTheme\BaseTheme;

class NotfoundController extends BaseTheme
{

    public $error_code = 404;
    public $message;
    
    public function checkAccess() : bool
    {
        return true;
    }
    
    public function preprocessPage()
    {
        $this->message =Translation::getTranslation("page_not_found");
        $this->setTitle(Translation::getTranslation("sorry").": ".Translation::getTranslation("page_not_found"));
        http_response_code($this->error_code);
    }
    
    public function getTemplateFile(): string
    {
        return "page-error.twig";
    }
}
