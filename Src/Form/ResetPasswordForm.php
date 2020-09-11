<?php

namespace Src\Form;

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
                ->setType("password")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("password"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "new-password")
        );
        $this->addField(
            InputWidget::create("password2")
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
        if (isset($this->request["reset"])) {
            if ($this->request["password"] != $this->request["password2"]) {
                $this->setError("password", Translation::getTranslation("password_match_error"));
            }
            if (!User::validatePassword($_POST["password"])) {
                $this->setError("password", Translation::getTranslation("password_validation_error"));
            }
        }
        return empty($this->errors);
    }

    public function submit()
    {
        $this->user->map([
            "password" => $this->request["password"],
            "active" => 1
        ]);
        $this->user->save();

        $reset_password_queue = ResetPassword::get(["user" => $this->user->ID, "key" => $_GET["KEY"]]);
        $reset_password_queue->delete();

        \CoreDB::database()->delete(Logins::getTableName())
        ->condition("username", $this->user->username)
        ->condition("ip_address", $this->user->get_user_ip(), "OR")
        ->execute();

        $message = Translation::getTranslation("password_reset_success");
        $username = $this->user->getFullName();

        \CoreDB::HTMLMail($this->user->email, Translation::getTranslation("reset_password"), $message, $username);

        $this->setMessage(Translation::getTranslation("password_reset_success"));
    }
}
