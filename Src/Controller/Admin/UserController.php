<?php

namespace Src\Controller\Admin;

use CoreDB\Kernel\Router;
use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\UserInsertForm;
use Src\Views\CollapsableCard;

class UserController extends AdminController
{
    public UserInsertForm $form;
    public $user;
    public $role_options = [];
    public $operation;
    public $form_build_id;

    public function preprocessPage()
    {
        if (isset($this->arguments[0])) {
            $this->user = User::getUserByUsername($this->arguments[0]);
            if (!$this->user) {
                Router::getInstance()->route(Router::NOT_FOUND);
            }
            $this->setTitle(Translation::getTranslation("edit_user") . " | " . $this->user->username);
        } elseif (isset($_GET["q"]) && $_GET["q"] == "insert") {
            $this->user = new User();
            $this->setTitle(Translation::getTranslation("add_user"));
        } else {
            $this->user = \CoreDB::currentUser();
            $this->setTitle(Translation::getTranslation("profile") . " | " . $this->user->username);
        }
        $this->form = new UserInsertForm($this->user);
        $this->form->processForm();
    }

    public function getTemplateFile(): string
    {
        return "page-admin-user.twig";
    }

    public function echoContent()
    {
        return CollapsableCard::create($this->title)
        ->setId("user_edit")
        ->setContent($this->form);
    }
}
