<?php

namespace Src\Controller;

use Src\Entity\Translation;
use Src\Form\LoginForm;
use Src\BaseTheme\BaseTheme;

class LoginController extends BaseTheme
{
    
    public $form;
    
    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-success"];
        $this->setTitle(Translation::getTranslation("welcome")."!");
    }
    public function getTemplateFile() : string
    {
        return "page-login.twig";
    }

    public function checkAccess() : bool
    {
        return true;
    }

    public function preprocessPage()
    {
        if(\CoreDB::currentUser()->isLoggedIn()){
            \CoreDB::goTo(MainpageController::getUrl());
        }
        $this->form = new LoginForm();
        $this->form->processForm();
    }

    protected function addDefaultJsFiles(){}

    public function echoContent()
    {
        return $this->form;
    }
}
