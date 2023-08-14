<?php

namespace Src\Form;

use CoreDB\Kernel\ConfigurationManager;
use Src\Entity\ResetPassword;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\Widget\InputWidget;

class ForgetPasswordForm extends Form
{
    public string $method = "POST";

    private ?User $user = null;
    public function __construct()
    {
        parent::__construct();
        $this->addClass("user");
        $this->addField(
            InputWidget::create("email")
            ->setType("email")
            ->setLabel(Translation::getTranslation("email"))
            ->addClass("form-control-user")
            ->addAttribute("placeholder", Translation::getTranslation("email"))
            ->addAttribute("required", "true")
        );
    }

    public function getFormId(): string
    {
        return "forget_password_form";
    }

    public function getTemplateFile(): string
    {
        return "forget-password-form.twig";
    }

    public function validate(): bool
    {
        $userClass = ConfigurationManager::getInstance()->getEntityInfo("users")["class"];
        if (!$userClass::getUserByEmail($this->request["email"])) {
            $this->setError("username", Translation::getTranslation("wrong_email"));
        } else {
            $this->user = $userClass::getUserByEmail($this->request["email"]);
            if ($this->user->status->getValue() == User::STATUS_BANNED) {
                $this->setError("username", Translation::getTranslation("account_banned"));
            }
        }
        return empty($this->errors);
    }

    public function submit()
    {
        $reset_password = new ResetPassword();
        $reset_password = ResetPassword::get(["user" => $this->user->ID]);
        if (!$reset_password) {
            $reset_password = new ResetPassword();
            $reset_password->user->setValue($this->user->ID);
            $reset_password->key->setValue(hash("SHA256", \CoreDB::currentDate() . json_encode($this->user->ID)));
            $reset_password->save();
        }

        $reset_link = BASE_URL . "/reset_password/?USER=" . $this->user->ID . "&KEY=" . $reset_password->key;
        $message = Translation::getEmailTranslation("password_reset", [$reset_link, $reset_link]);
        $username = $this->user->getFullName();
        $sent = \CoreDB::HTMLMail(
            $this->user->email,
            Translation::getTranslation("reset_password"),
            $message,
            $username
        );
        if ($sent) {
            $this->setMessage(Translation::getTranslation("password_reset_mail_success"));
        } else {
            $this->setError("email", Translation::getTranslation("an_error_occured"));
        }
    }

    protected function csrfTokenCheckFailed()
    {
    }
}
