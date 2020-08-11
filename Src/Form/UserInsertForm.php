<?php

namespace Src\Form;

use Src\Entity\Translation;
use Src\Entity\User;

class UserInsertForm extends TableInsertForm{

    public function __construct(User $user)
    {
        parent::__construct($user);
        $this->fields["{$user->table}[password]"]->setValue("");
        unset($this->fields["{$user->table}[created_at]"], $this->fields["{$user->table}[access]"]);
    }

    public function validate(): bool
    {
        $parent_check = parent::validate();
        if( $this->request[$this->object->table]["password"] && !User::validatePassword($this->request[$this->object->table]["password"]) ){
            $this->setError("{$this->object->table}[password]", Translation::getTranslation("password_validation_error"));
        }
        return $parent_check && empty($this->errors);
    }

    public function submit()
    {
        if($this->request[$this->object->table]["password"]){
            $this->request[$this->object->table]["password"] = password_hash($this->request[$this->object->table]["password"], PASSWORD_BCRYPT);
        }else{
            $this->request[$this->object->table]["password"] = $this->object->password;
        }
        parent::submit();
    }


    protected function getSaveRedirectUrl() :string {
        return BASE_URL."/admin/user/{$this->object->username}";
    }

    protected function getDeleteRedirectUrl() :string {
        return BASE_URL."/admin/manage/user";
    }
}