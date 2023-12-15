<?php

namespace Src\Controller;

use CoreDB\Kernel\BaseController;
use Src\Entity\Translation;
use Src\Form\ForgetPasswordForm;

class ForgetpasswordController extends BaseController
{
    public $form;

    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->setTitle(Translation::getTranslation("forgot_password_question") . "?");
    }
    public function getTemplateFile(): string
    {
        return "page-login.twig";
    }

    public function checkAccess(): bool
    {
        return !\CoreDB::currentUser()->isLoggedIn();
    }
    public function preprocessPage()
    {

        $this->form = new ForgetPasswordForm();
        $this->form->processForm();
    }

    protected function addDefaultJsFiles()
    {
    }


    public function echoContent()
    {
        return $this->form;
    }
}
