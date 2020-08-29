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
        /*
        $password_entry = new FormBuilder("POST");
        $password_entry->addClass("col-12");
        if ($this->user->ID == \CoreDB::currentUser()->ID) {
            $password_entry->addField(
                InputField::create("password[current_pass]")
                    ->setLabel(Translation::getTranslation("current_pass"))
                    ->setType("password")
                    ->addAttribute("autocomplete", "off")
            );
        }
        $password_entry->addField(
            InputField::create("password[password]")
                ->setLabel(Translation::getTranslation("password"))
                ->setType("password")
                ->addAttribute("autocomplete", "new-password")
        )->addField(
            InputField::create("password[password2]")
                ->setLabel(Translation::getTranslation("password_again"))
                ->setType("password")
                ->addAttribute("autocomplete", "new-password")
        )->addField(
            InputField::create("change_password")
                ->setValue(Translation::getTranslation("update_password"))
                ->setType("submit")
                ->addClass("btn btn-outline-success")
        )->addField(
            InputField::create("form_build_id")->setValue($password_form_build_id)->setType("hidden")
        )->addField(
            InputField::create("form_token")->setValue($password_form_token)->setType("hidden")
        );

        $user_edit_form = $this->user->getForm("user_info");
        $user_edit_form->addField(
            InputField::create("form_build_id")->setValue($this->form_build_id)->setType("hidden")
        )->addField(
            InputField::create("form_token")->setValue($this->form_token)->setType("hidden")
        )->addField(
            SelectField::create("user_info[ROLES][]")
                ->addAttribute("multiple", "true")
                ->setOptions($this->role_options),
            1
        );

        $container = new Group("container-fluid");
        $container->addField(
            Group::create("d-sm-flex align-items-center justify-content-between mb-4")
                ->addField(
                    Group::create("h3 mb-0 text-gray-800")->setTagName("h1")
                        ->addField(TextElement::create($this->title))
                )
        )->addField(
            Group::create("row")
                ->addField(
                    Group::create("col-12")->addField($this)
                )
                ->addField(
                    Group::create("col-sm-6")->addField($user_edit_form)
                )->addField(
                    Group::create("col-sm-6")->addField($password_entry)
                )
        );
        echo $container; */
    }
}
