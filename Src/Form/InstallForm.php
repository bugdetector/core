<?php

namespace Src\Form;

use CoreDB\Kernel\Database\MySQL\MySQLDriver;
use CoreDB\Kernel\Messenger;
use Src\Controller\LoginController;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\Widget\InputWidget;

class InstallForm extends Form
{
    public string $method = "POST";
    public function __construct()
    {
        parent::__construct();
        $this->addClass("user");
        if(!isset($this->request["save"])){
            $this->setMessage( Translation::getTranslation("install_description"), Messenger::INFO );
        }
        $this->addField(
            InputWidget::create("db_server")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("db_server"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "false")
        );
        $this->addField(
            InputWidget::create("db_name")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("db_name"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "false")
        );
        $this->addField(
            InputWidget::create("db_user")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("db_user"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "false")
        );
        $this->addField(
            InputWidget::create("db_password")
                ->setType("password")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("db_password"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "new-password")
        );
        $this->addField(
            InputWidget::create("username")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("username"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "new-password")
        );
        $this->addField(
            InputWidget::create("name")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("name"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "new-password")
        );
        $this->addField(
            InputWidget::create("email")
                ->setType("email")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("email"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "new-password")
        );
        $this->addField(
            InputWidget::create("password")
                ->setType("password")
                ->addClass("form-control-user")
                ->addAttribute("placeholder", Translation::getTranslation("password"))
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "new-password")
        );
        $this->addField(
            InputWidget::create("save")
                ->setType("submit")
                ->setValue(Translation::getTranslation("save"))
                ->removeClass("form-control")
                ->addClass("btn btn-info btn-user btn-block")
                ->addAttribute("required", "true")
        );
    }

    public function getTemplateFile(): string
    {
        return "install-form.twig";
    }

    public function getFormId(): string
    {
        return "install_form";
    }

    public function validate(): bool
    {
        $driverClass = MySQLDriver::class;
        if ($driverClass::checkConnection(
            $this->request["db_server"],
            $this->request["db_name"],
            $this->request["db_user"],
            $this->request["db_password"],
        )){
            return true;
        }else{
            $this->setError("db_server", Translation::getTranslation("cant_connect_to_database"));
            return false;
        }
    }

    public function submit()
    {
        $exampleConfig = file_get_contents("../config/config_example.php");
        $config = str_replace(
            [
                "%db_server",
                "%db_name",
                "%db_user",
                "%db_password",
            ],
            [
                $this->request["db_server"],
                $this->request["db_name"],
                $this->request["db_user"],
                $this->request["db_password"],
            ],
            $exampleConfig
        );
        file_put_contents("../config/config.php", $config);
        define("DB_SERVER", $this->request["db_server"]);
        define("DB_NAME", $this->request["db_name"]);
        define("DB_USER", $this->request["db_user"]);
        define("DB_PASSWORD", $this->request["db_password"]);
        \CoreDB::config()->importTableConfiguration();
        Translation::importTranslations(); 
        \CoreDB::database()->insert(User::getTableName(), [
            "username" => $this->request["username"],
            "name" => $this->request["name"],
            "email" => $this->request["email"],
            "password" => password_hash($this->request["password"], PASSWORD_BCRYPT),
        ])->execute();
        $this->setMessage(Translation::getTranslation("all_configuration_imported"));
        \CoreDB::goTo(LoginController::getUrl());
    }
}
