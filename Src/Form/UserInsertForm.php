<?php

namespace Src\Form;

use Src\Controller\Admin\Manage\UserController;
use Src\Controller\Admin\UserController as AdminUserController;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Views\ViewGroup;

class UserInsertForm extends TableInsertForm
{

    public function __construct(User $user)
    {
        parent::__construct($user);
        $password_input = $this->fields["{$user->table}[password]"]
        ->setType("password")
        ->setDescription("")
        ->setValue("")
        ->addAttribute("autocomplete", "new-password");
        $new_password_input = ViewGroup::create("div", "");
        $current_user = \CoreDB::currentUser();
        if(!$current_user->isAdmin() || $current_user->ID == $user->ID){
            $new_password_input->addField((clone $password_input)
            ->setLabel(Translation::getTranslation("current_pass"))
            ->setName("current_pass"));
        }
        $new_password_input->addField($password_input)
        ->addField((clone $password_input)->setLabel(Translation::getTranslation("password_again"))->setName("password_again"))
        ->addClassToChildren(true);
        $this->fields["{$user->table}[password]"] = $new_password_input;
        $this->fields["{$user->table}[password]"]->addAttribute("disabled", "true");
        unset($this->fields["{$user->table}[created_at]"], $this->fields["{$user->table}[access]"]);
    }

    public function validate(): bool
    {
        $parent_check = parent::validate();
        if ($this->request[$this->object->table]["password"]) {
            $current_user = \CoreDB::currentUser();
            if(!$current_user->isAdmin() || $current_user->ID == $this->object->ID){
                if(!password_verify($this->request["current_pass"], $this->object->password)){
                    $this->setError("{$this->object->table}[password]", Translation::getTranslation("current_pass_wrong"));
                }
            }
            if ($this->request[$this->object->table]["password"] != $this->request["password_again"]) {
                $this->setError("{$this->object->table}[password]", Translation::getTranslation("password_match_error"));
            }
            if (!\CoreDB::currentUser()->isAdmin() && !User::validatePassword($this->request[$this->object->table]["password"])) {
                $this->setError("{$this->object->table}[password]", Translation::getTranslation("password_validation_error"));
            }
        }
        return $parent_check && empty($this->errors);
    }


    protected function submitSuccess()
    {
        \CoreDB::goTo(AdminUserController::getUrl().$this->object->username);
    }

    protected function getDeleteRedirectUrl() :string
    {
        \CoreDB::goTo(UserController::getUrl());
    }
}
