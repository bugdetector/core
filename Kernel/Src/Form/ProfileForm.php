<?php

namespace Src\Form;

use Src\Controller\ProfileController;
use CoreDB;
use Src\Entity\File;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\Form;
use Src\Form\Widget\InputWidget;
use Src\Views\ViewGroup;

class ProfileForm extends Form
{

    public const VALID_KEYS = [
        "name",
        "surname",
        "profile_photo",
        "email",
        "phone",
        "password",
    ];
    public $user;
    public string $method = "POST";

    public function __construct()
    {
        parent::__construct();
        $controller = \CoreDB::controller();
        $controller->addJsFiles("dist/insert_form/insert_form.js");
        $controller->addFrontendTranslation("record_remove_accept");
        $controller->addFrontendTranslation("record_remove_accept_field");

        $this->user = User::get(\CoreDB::currentUser()->ID->getValue());
        $userFields = $this->user->getFormFields("profile");
        $userFields["password"]
            ->setValue("")
            ->setDescription("");
        $userFields["profile_photo"]->fileClass = "rounded-circle";

        $password_input = $userFields["password"]
        ->setType("password")
        ->setDescription("")
        ->setValue("")
        ->addAttribute("autocomplete", "new-password");
        $new_password_input = ViewGroup::create("div", "");
        $current_user = \CoreDB::currentUser();
        if ($current_user->ID->getValue() == $this->user->ID->getValue()) {
            $new_password_input->addField((clone $password_input)
            ->setLabel(Translation::getTranslation("current_pass"))
            ->setName("current_pass"));
        }
        $new_password_input->addField($password_input)
        ->addField((clone $password_input)
        ->setLabel(Translation::getTranslation("password_again"))
        ->setName("password_again"))
        ->addClassToChildren(true);
        $userFields["password"] = $new_password_input;
        $userFields["password"]->addAttribute("disabled", "true");

        foreach ($userFields as $fieldName => $field) {
            if (in_array($fieldName, self::VALID_KEYS)) {
                $this->addField($field);
            }
        }
        $this->addField(
            InputWidget::create("save")
                ->setType("submit")
                ->setValue(
                    Translation::getTranslation("save")
                )->addClass("btn btn-primary mt-4")
                ->removeClass("form-control")
        );
    }

    public function getFormId(): string
    {
        return "profile_form";
    }

    public function validate(): bool
    {
        if (
            !empty(array_diff(
                array_keys($this->request["profile"]),
                self::VALID_KEYS
            ))
        ) {
            $this->setError("form_id", Translation::getTranslation("invalid_operation"));
        }
        if ($this->request["profile"]["password"]) {
            if (!password_verify($this->request["current_pass"], $this->user->password)) {
                $this->setError(
                    "password",
                    Translation::getTranslation("current_pass_wrong")
                );
            }
            if ($this->request["profile"]["password"] != $this->request["password_again"]) {
                $this->setError(
                    "password",
                    Translation::getTranslation("password_match_error")
                );
            }
            if (
                !User::validatePassword($this->request["profile"]["password"])
            ) {
                $this->setError(
                    "password",
                    Translation::getTranslation("password_validation_error")
                );
            }
        }
        if ($profilePhotoId = @$this->request["profile"]["profile_photo"]) {
            /** @var File */
            $profilePhoto = File::get($profilePhotoId);
            if (!\CoreDB::isImage($profilePhoto->getFilePath())) {
                $this->setError("profile_photo", Translation::getTranslation("upload_an_image"));
                $profilePhoto->delete();
            } else {
                $contents = file_get_contents($profilePhoto->getFilePath());
                $image = imagecreatefromstring($contents);
                $image = imagescale($image, 200, 200);
                $exif = exif_read_data($profilePhoto->getFilePath());
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 8:
                            $image = imagerotate($image, 90, 0);
                            break;
                        case 3:
                            $image = imagerotate($image, 180, 0);
                            break;
                        case 6:
                            $image = imagerotate($image, -90, 0);
                            break;
                    }
                }
                imagejpeg($image, $profilePhoto->getFilePath());
            }
        }
        return empty($this->errors);
    }

    public function submit()
    {
        $this->user->map(
            $this->request["profile"]
        );
        $this->user->save();
        $this->setMessage(
            Translation::getTranslation("update_success")
        );
        CoreDB::goTo(ProfileController::getUrl());
    }
}
