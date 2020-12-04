<?php

namespace Src\Form;

use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\Widget\InputWidget;
use Src\JWT;
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

        /** @var InputWidget */
        $profilePhotoInput = &$this->fields[$user->entityName . "[profile_photo]"];
        $profilePhotoInput->addFileRemoveKey(
            $user->entityName,
            $user->ID->getValue(),
            "profile_photo"
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
        $normalizedFiles = \CoreDB::normalizeFiles($_FILES);
        if (isset($normalizedFiles["users"]["profile_photo"]) && $normalizedFiles["users"]["profile_photo"]["size"]) {
            if (!\CoreDB::isImage($normalizedFiles["users"]["profile_photo"]["tmp_name"])) {
                $this->setError("profile_photo", Translation::getTranslation("upload_an_image"));
            } elseif ($normalizedFiles["users"]["profile_photo"]["size"]) {
                $contents = file_get_contents($normalizedFiles["users"]["profile_photo"]["tmp_name"]);
                $image = imagecreatefromstring($contents);
                $image = imagescale($image, 200, 200);
                imagejpeg($image, $normalizedFiles["users"]["profile_photo"]["tmp_name"]);
            }
        }
        return $parent_check && empty($this->errors);
    }
}
