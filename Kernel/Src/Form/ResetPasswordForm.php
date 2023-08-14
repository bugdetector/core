<?php

namespace Src\Form;

use CoreDB\Kernel\ConfigurationManager;
use Src\Entity\Logins;
use Src\Entity\ResetPassword;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\Widget\InputWidget;

class ResetPasswordForm extends Form
{
    public string $method = "POST";

    private ?User $user;
    public function __construct(User $user = null)
    {
        $this->user = $user;
        parent::__construct();
        $this->addClass("user");
        $this->addField(
            InputWidget::create("password")
                ->setLabel(Translation::getTranslation("password"))
                ->setType("password")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("password"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "new-password")
        );
        $this->addField(
            InputWidget::create("password2")
                ->setLabel(Translation::getTranslation("password_again"))
                ->setType("password")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("password_again"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "new-password")
        );
    }

    public function getFormId(): string
    {
        return "reset_password_form";
    }

    public function getTemplateFile(): string
    {
        return "reset-password-form.twig";
    }

    public function validate(): bool
    {
        if ($this->request["password"] != $this->request["password2"]) {
            $this->setError("password", Translation::getTranslation("password_match_error"));
        }
        $userClass = ConfigurationManager::getInstance()->getEntityInfo("users")["class"];
        if (!$userClass::validatePassword($_POST["password"])) {
            $this->setError("password", Translation::getTranslation("password_validation_error"));
        }
        return empty($this->errors);
    }

    public function submit()
    {
        $this->user->map([
            "password" => $this->request["password"],
            "status" => User::STATUS_ACTIVE
        ]);
        $this->user->save();

        $reset_password_queue = ResetPassword::get(["user" => $this->user->ID, "key" => $_GET["KEY"]]);
        $reset_password_queue->delete();

        \CoreDB::database()->delete(Logins::getTableName())
        ->condition("username", $this->user->username)
        ->condition("ip_address", $this->user->getUserIp(), "OR")
        ->execute();

        $message = Translation::getTranslation("password_reset_success");
        $username = $this->user->getFullName();

        $sent = \CoreDB::HTMLMail(
            $this->user->email,
            Translation::getTranslation("reset_password"),
            $message,
            $username
        );
        if ($sent) {
            $this->setMessage(
                Translation::getTranslation("password_reset_mail_success")
            );
        } else {
            $this->setError("email", Translation::getTranslation("an_error_occured"));
        }
    }
}
