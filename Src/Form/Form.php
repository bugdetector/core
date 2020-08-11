<?php

namespace Src\Form;

use CoreDB\Kernel\Messenger;

use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Theme\CoreRenderer;
use Src\Theme\View;
use Src\Theme\Views\AlertMessage;

abstract class Form extends View
{
    const ENCRYPTION_METHOD = "aes128";

    public string $form_id;
    public string $form_build_id;
    public string $form_token;

    public string $enctype;
    public string $method = "GET";

    public array $fields = [];
    public array $errors = [];

    public array $request;

    public function __construct()
    {
        $this->createCsrf($this->getFormId());
        $this->addField(
            InputWidget::create("form_id")->setValue($this->getFormId())->setType("hidden")
        )->addField(
            InputWidget::create("form_build_id")->setValue($this->form_build_id)->setType("hidden")
        )->addField(
            InputWidget::create("form_token")->setValue($this->form_token)->setType("hidden")
        );

    }

    
    public function processForm()
    {
        if ($this->method == "GET") {
            $this->request = $_GET;
        } elseif ($this->method == "POST") {
            $this->request = $_POST;
        }
        if (isset($this->request["form_id"]) && $this->request["form_id"] == $this->getFormId()) {
            if ($this->checkCsrfToken() && $this->validate() && empty($this->errors)) {
                $this->submit();
            }else if(!empty($this->errors)){
                foreach($this->errors as $field_name => $value){
                    if(isset($this->fields[$field_name])){
                        $this->fields[$field_name]->addClass("has-error");
                    }
                }
                foreach($this->fields as $field_name => $field){
                    if( !in_array($field_name, ["form_id", "form_build_id", "form_token"]) && isset($this->request[$field_name])){
                        $this->fields[$field_name]->setValue($this->request[$field_name]);
                    }
                }
            }
        }
    }

    abstract public function getFormId(): string;
    abstract public function validate() : bool;
    abstract public function submit();

    public function getTemplateFile(): string
    {
        return "form.twig";
    }

    public function render()
    {
        CoreRenderer::getInstance([])->renderForm($this);
    }

    public function setEnctype(string $enctype)
    {
        $this->enctype = $enctype;
        return $this;
    }

    public function addField(FormWidget $field)
    {
        $this->fields[$field->name] = $field;
        return $this;
    }

    public function setError(string $field_name, string $message)
    {
        $this->errors[$field_name] = AlertMessage::create($message);
    }

    public function setMessage(string $message, int $type = Messenger::SUCCESS)
    {
        \CoreDB::messenger()->createMessage($message, $type);
    }

    protected function createCsrf()
    {
        $encryption_key = bin2hex(random_bytes(10));
        $this->form_build_id = @openssl_encrypt($this->getFormId(), self::ENCRYPTION_METHOD, $encryption_key);
        $this->form_token = hash("SHA256", $this->form_build_id . User::get_user_ip());
        $_SESSION[$this->form_build_id] = [
            "encryption_key" => $encryption_key,
            "value" => $this->form_token
        ];
    }

    protected function getCsrf(string $form_build_id, string $form_id)
    {
        if (isset($_SESSION[$form_build_id])) {
            $encryption_key = $_SESSION[$form_build_id]["encryption_key"];
            $value = $_SESSION[$form_build_id]["value"];

            $decrypted_form_id = openssl_decrypt($form_build_id, self::ENCRYPTION_METHOD, $encryption_key);
            if ($form_id != $decrypted_form_id) {
                $this->setError("form_build_id", Translation::getTranslation("invalid_key"));
            }
            unset($_SESSION[$form_build_id]);
            return $value;
        }
    }

    protected function checkCsrfToken(): bool
    {
        if ($this->request["form_token"] == $this->getCsrf($this->request["form_build_id"], $this->getFormId())) {
            return true;
        } else {
            $this->csrfTokenCheckFailed();
            return false;
        }
    }

    protected function csrfTokenCheckFailed()
    {
        $this->setError("form_token", Translation::getTranslation("invalid_operation"));
    }
}
