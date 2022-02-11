<?php

namespace Src\Controller;

use CoreDB\Kernel\BaseController;
use CoreDB\Kernel\ConfigurationManager;
use CoreDB\Kernel\Messenger;
use Src\Entity\ResetPassword;
use Src\Entity\Translation;
use Src\Form\ResetPasswordForm;

class ResetPasswordController extends BaseController
{
    
    public $form;
    
    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-info"];
        $this->setTitle(Translation::getTranslation("reset_password"));
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

        if (!$_GET) {
            $this->createMessage(Translation::getTranslation("link_used"), Messenger::ERROR);
        } elseif (isset($_GET["USER"]) && isset($_GET["KEY"])) {
            $reset_password_queue = ResetPassword::get(["user" => $_GET["USER"], "key" => $_GET["KEY"]]);
            if (!$reset_password_queue) {
                $this->createMessage(Translation::getTranslation("link_used"), Messenger::ERROR);
            } else {
                $userClass = ConfigurationManager::getInstance()->getEntityInfo("users")["class"];
                $user = $userClass::get($_GET["USER"]);
                $this->form = new ResetPasswordForm($user);
                $this->form->processForm();
            }
        }
        if (!$this->form) {
            $this->form = new ResetPasswordForm();
        }
    }

    protected function addDefaultJsFiles()
    {
    }

    public function echoContent()
    {
        return $this->form;
    }
}
