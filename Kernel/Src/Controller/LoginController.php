<?php

namespace Src\Controller;

use CoreDB\Kernel\BaseController;
use CoreDB\Kernel\ConfigurationManager;
use Src\Entity\Translation;
use Src\Form\LoginForm;
use Src\Entity\User;

class LoginController extends BaseController
{
    public $form;
    public ?User $loginAsUser = null;

    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->setTitle(Translation::getTranslation("login"));
        if (isset($_GET["login_as_user"])) {
            $userClass = ConfigurationManager::getInstance()->getEntityInfo("users")["class"];
            $this->loginAsUser = $userClass::get($_GET["login_as_user"]);
        }
    }
    public function getTemplateFile(): string
    {
        return "page-login.twig";
    }

    public function checkAccess(): bool
    {
        if (!$this->loginAsUser) {
            return true;
        } else {
            return \CoreDB::currentUser()->isAdmin();
        }
    }

    public function preprocessPage()
    {
        $user = \CoreDB::currentUser();
        if ($this->loginAsUser && $user->isAdmin()) {
            if ($this->loginAsUser->isAdmin()) {
                $this->createMessage(
                    Translation::getTranslation("cannot_login_as_another_admin_user")
                );
                \CoreDB::goTo(
                    @$_SERVER["HTTP_REFERER"] ?: BASE_URL
                );
            }
            $_SESSION[BASE_URL . "-BACKUP-UID"] = $user->ID;
            \CoreDB::userLogin($this->loginAsUser);
            \CoreDB::goTo(BASE_URL);
        } else {
            if (\CoreDB::currentUser()->isLoggedIn()) {
                \CoreDB::goTo(BASE_URL);
            }
            $this->form = new LoginForm();
            $this->form->processForm();
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
