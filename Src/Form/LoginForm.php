<?php

namespace Src\Form;

use CoreDB;
use CoreDB\Kernel\Router;
use Src\Controller\AdminController;
use Src\Controller\MainpageController;
use Src\Entity\Logins;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Watchdog;
use Src\Form\Widget\InputWidget;
use Src\JWT;
use stdClass;

class LoginForm extends Form
{
    public string $method = "POST";

    private ?User $user;

    public function __construct()
    {
        parent::__construct();
        $this->addClass("user");
        $this->addField(
            InputWidget::create("username")
            ->addClass("form-control-user")
            ->addAttribute("placeholder", Translation::getTranslation("username"))
            ->addAttribute("required", "true")
            ->addAttribute("autofocus", "true")
        );
        $this->addField(
            InputWidget::create("password")
            ->setType("password")
            ->addClass("form-control-user")
            ->addAttribute("placeholder", Translation::getTranslation("password"))
            ->addAttribute("required", "true")
            ->addAttribute("autofocus", "true")
        );
    }

    public function getFormId(): string
    {
        return "login_form";
    }

    public function getTemplateFile(): string
    {
        return "login-form.twig";
    }

    public function validate() : bool
    {
        //if ip address is blocked not let to login
        if (User::isIpAddressBlocked()) {
            $this->setError("username", Translation::getTranslation("ip_blocked"));
        }

        $this->user = User::getUserByUsername($this->request["username"]);
        if ($this->user && !$this->user->active) {
            $this->setError("username", Translation::getTranslation("account_blocked"));
        }

        //if login fails for more than 10 times block this ip
        if (isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS]) && $_SESSION[LOGIN_UNTRUSTED_ACTIONS] > 10) {
            if (User::getLoginTryCountOfIp() > 10) {
                User::blockIpAddress();
            }
            if (User::getLoginTryCountOfUser($this->request["username"]) > 10) {
                //blocking user
                $this->user->active = 0;
                $this->user->save();
            }
            $this->setError("username", Translation::getTranslation("ip_blocked"));
        }
        if (empty($this->errors) && (!$this->user || !password_verify($this->request["password"], $this->user->password))) {
            if (isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS])) {
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS]++;
                if ($_SESSION[LOGIN_UNTRUSTED_ACTIONS] > 3) {
                    $this->setError("password", Translation::getTranslation("too_many_login_fails"));
                }
            } else {
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS] = 1;
            }
            $this->setError("password", Translation::getTranslation("wrong_credidental"));
        }

        if (!empty($this->errors)) {
            //Logging failed login actions
            $login_log = new Logins();
            $login_log->ip_address = User::getUserIp();
            $login_log->username = $_POST["username"];
            $login_log->save();
            return false;
        }

        return true;
    }

    public function submit()
    {
        //login successful
        global $current_user;
        $current_user = $this->user;
        $current_user->last_access->setValue(\CoreDB::get_current_date());
        $current_user->save();
        $_SESSION[BASE_URL . "-UID"] = $this->user->ID;
        if (isset($_POST["remember-me"]) && $_POST["remember-me"]) {
            $jwt = new JWT();
            $payload = new stdClass();
            $payload->ID = $current_user->ID->getValue();
            $jwt->setPayload($payload);
            setcookie("session-token", $jwt->createToken(), strtotime("+1 day"), BASE_URL, $_SERVER["HTTP_HOST"], false, true);
        }

        Watchdog::log("login", $this->user->username);

        unset($_SESSION[PASSWORD_FALSE_COUNT]);
        unset($_SESSION[LOGIN_UNTRUSTED_ACTIONS]);

        //Clearing failed login actions
        CoreDB::database()->delete(Logins::getTableName())
            ->condition("username", $this->user->username)
            ->execute();
        if (isset($_GET["destination"])) {
            \CoreDB::goTo(BASE_URL . $_GET["destination"]);
        } elseif (\CoreDB::currentUser()->isAdmin()) {
            \CoreDB::goTo(AdminController::getUrl());
        } else {
            \CoreDB::goTo(MainpageController::getUrl());
        }
    }

    protected function csrfTokenCheckFailed()
    {
        parent::csrfTokenCheckFailed();
        if (isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS])) {
            $_SESSION[LOGIN_UNTRUSTED_ACTIONS]++;
        } else {
            $_SESSION[LOGIN_UNTRUSTED_ACTIONS] = 1;
        }
    }
}
