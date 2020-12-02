<?php

namespace Src\Form;

use Src\Entity\Translation;
use Src\Entity\User;
use Src\Views\ViewGroup;

class UserInsertForm extends InsertForm
{

    public function __construct(User $user)
    {
        parent::__construct($user);
        $password_input = $this->fields[$user->entityName . "[password]"]
        ->setType("password")
        ->setDescription("")
        ->setValue("")
        ->addAttribute("autocomplete", "new-password");
        $new_password_input = ViewGroup::create("div", "");
        $current_user = \CoreDB::currentUser();
        if ($current_user->ID->getValue() == $user->ID->getValue()) {
            $new_password_input->addField((clone $password_input)
            ->setLabel(Translation::getTranslation("current_pass"))
            ->setName("current_pass"));
        }
        $new_password_input->addField($password_input)
        ->addField((clone $password_input)
        ->setLabel(Translation::getTranslation("password_again"))
        ->setName("password_again"))
        ->addClassToChildren(true);
        $this->fields[$user->entityName . "[password]"] = $new_password_input;
        $this->fields[$user->entityName . "[password]"]->addAttribute("disabled", "true");
        unset(
            $this->fields[$user->entityName . "[created_at]"],
            $this->fields[$user->entityName . "[last_access]"]
        );
    }

    public function validate(): bool
    {
        $parent_check = parent::validate();
        if ($this->request[$this->object->entityName]["password"]) {
            $current_user = \CoreDB::currentUser();
            if ($current_user->ID->getValue() == $this->object->ID->getValue()) {
                if (!password_verify($this->request["current_pass"], $this->object->password)) {
                    $this->setError(
                        $this->object->entityName . "[password]",
                        Translation::getTranslation("current_pass_wrong")
                    );
                }
            }
            if ($this->request[$this->object->entityName]["password"] != $this->request["password_again"]) {
                $this->setError(
                    $this->object->entityName . "[password]",
                    Translation::getTranslation("password_match_error")
                );
            }
            if (
                !User::validatePassword($this->request[$this->object->entityName]["password"])
            ) {
                $this->setError(
                    $this->object->entityName . "[password]",
                    Translation::getTranslation("password_validation_error")
                );
            }
        }
        return $parent_check && empty($this->errors);
    }
}
