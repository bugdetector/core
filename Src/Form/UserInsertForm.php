<?php

namespace Src\Form;

use Src\Entity\Translation;
use Src\Entity\User;
use Src\Views\ViewGroup;

class UserInsertForm extends TableInsertForm
{

    public function __construct(User $user)
    {
        parent::__construct($user);
        $password_input = $this->fields["{$user->table}[password]"]->setValue("");
        $new_password_input = ViewGroup::create("div", "")
        ->addField($password_input)
        ->addField((clone $password_input)->setName("password_again"))
        ->addClassToChildren(true);
        $this->fields["{$user->table}[password]"] = $new_password_input;
        unset($this->fields["{$user->table}[created_at]"], $this->fields["{$user->table}[access]"]);
    }

    public function validate(): bool
    {
        $parent_check = parent::validate();
        if ($this->request[$this->object->table]["password"]) {
            if ($this->request[$this->object->table]["password"] != $this->request["password_again"]) {
                $this->setError("password", Translation::getTranslation("password_match_error"));
            }
            if (!User::validatePassword($this->request[$this->object->table]["password"])) {
                $this->setError("{$this->object->table}[password]", Translation::getTranslation("password_validation_error"));
            }
        }
        return $parent_check && empty($this->errors);
    }

    public function submit()
    {
        if ($this->request[$this->object->table]["password"]) {
            $this->request[$this->object->table]["password"] = password_hash($this->request[$this->object->table]["password"], PASSWORD_BCRYPT);
        } else {
            $this->request[$this->object->table]["password"] = $this->object->password;
        }
        parent::submit();
    }


    protected function getSaveRedirectUrl() :string
    {
        return BASE_URL."/admin/user/{$this->object->username}";
    }

    protected function getDeleteRedirectUrl() :string
    {
        return BASE_URL."/admin/manage/user";
    }
}
