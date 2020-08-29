<?php

namespace Src\Controller;

use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\ForgetPasswordForm;
use Src\Form\LoginForm;
use Src\Theme\BaseTheme\BaseTheme;

class ForgetpasswordController extends BaseTheme
{

    public $form;
    
    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-info"];
        $this->setTitle(Translation::getTranslation("forgot_password_question")."?");
    }
    public function getTemplateFile() : string
    {
        return "page-login.twig";
    }

    public function checkAccess() : bool
    {
        return !\CoreDB::currentUser()->isLoggedIn();
    }
    public function preprocessPage()
    {

        $this->form = new ForgetPasswordForm();
        $this->form->processForm();
    }


    public function echoContent()
    {
        return $this->form;
    }
}
