<?php

namespace Src\Form;

use CoreDB\Kernel\Messenger;

use Src\Entity\ResetPassword;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\Widget\InputWidget;

class ForgetPasswordForm extends Form
{
    public string $method = "POST";

    public function __construct()
    {
        parent::__construct();
        $this->addClass("user");
        $this->addField(
            InputWidget::create("username")
            ->setLabel(Translation::getTranslation("username"))
            ->addClass("form-control-user")
            ->addAttribute("placeholder", Translation::getTranslation("username"))
            ->addAttribute("required", "true")
            ->addAttribute("autofocus", "true")
        );
        $this->addField(
            InputWidget::create("email")
            ->setLabel(Translation::getTranslation("email"))
            ->addClass("form-control-user")
            ->addAttribute("placeholder", Translation::getTranslation("email"))
            ->addAttribute("required", "true")
            ->setType("email")
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

    public function validate() : bool
    {
        if (isset($this->request["reset"])) {
            if (!User::get(["username" => $this->request["username"], "email" => $this->request["email"]])) {
                $this->setError("username", Translation::getTranslation("wrong_username_or_email"));
            }
        }
        return empty($this->errors);
    }

    public function submit()
    {
        $user = User::get(["username" => $this->request["username"], "email" => $this->request["email"]]);
        $reset_password = new ResetPassword();
        $reset_password = ResetPassword::get(["user" => $user->ID]);
        if (!$reset_password) {
            $reset_password = new ResetPassword();
            $reset_password->user = $user->ID;
            $reset_password->key = hash("SHA256", \CoreDB::get_current_date().json_encode($user->ID));
            $reset_password->save();
        }
        
        $reset_link = BASE_URL."/reset_password/?USER=".$user->ID."&KEY=".$reset_password->key;
        $message = Translation::getEmailTranslation("password_reset", [$reset_link, $reset_link]);
        $username = $user->getFullName();
        
        \CoreDB::HTMLMail($user->email, Translation::getTranslation("reset_password"), $message, $username);
        
        \CoreDB::messenger()->createMessage(Translation::getTranslation("password_reset_mail_success"), Messenger::SUCCESS);
    }

    protected function csrfTokenCheckFailed()
    {
    }
}
