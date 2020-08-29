<?php

namespace Src\Controller;

use CoreDB\Kernel\Messenger;
use Src\Entity\ResetPassword;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\ResetPasswordForm;
use Src\Theme\BaseTheme\BaseTheme;

class Reset_PasswordController extends BaseTheme
{
    
    public $form;
    
    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-info"];
        $this->setTitle(Translation::getTranslation("reset_password"));
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

        if (!$_GET) {
            $this->createMessage(Translation::getTranslation("link_used"), Messenger::ERROR);
        } elseif (isset($_GET["USER"]) && isset($_GET["KEY"])) {
            $reset_password_queue = ResetPassword::get(["user" => $_GET["USER"], "key" => $_GET["KEY"]]);
            if (!$reset_password_queue) {
                $this->createMessage(Translation::getTranslation("link_used"), Messenger::ERROR);
            } else {
                $user = User::get(["ID" => $_GET["USER"]]);
                $this->form = new ResetPasswordForm($user);
                $this->form->processForm();
            }
        }
        if (!$this->form) {
            $this->form = new ResetPasswordForm();
        }
    }


    public function echoContent()
    {
        return $this->form;
    }
}
