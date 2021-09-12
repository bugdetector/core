<?php

namespace Src\Form;

use CoreDB;
use CoreDB\Kernel\ConfigurationManager;
use Src\Controller\AdminController;
use Src\Entity\Logins;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Watchdog;
use Src\Form\Widget\InputWidget;
use Src\JWT;
use stdClass;

class LoginForm extends Form
{
    private const PASSWORD_FALSE_COUNT = "PASSWORD_FALSE_COUNT";
    private const LOGIN_UNTRUSTED_ACTIONS = "LOGIN_UNTRUSTED_ACTIONS";
    public string $method = "POST";

    private ?User $user;

    public function __construct()
    {
        parent::__construct();
        $this->addClass("user");
        $this->addField(
            InputWidget::create("username")
            ->setLabel(Translation::getTranslation("username_or_email"))
            ->addClass("form-control-user")
            ->addAttribute("placeholder", Translation::getTranslation("username"))
            ->addAttribute("required", "true")
            ->addAttribute("autofocus", "true")
        );
        $this->addField(
            InputWidget::create("password")
            ->setLabel(Translation::getTranslation("password"))
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

    public function validate(): bool
    {
        //if ip address is blocked not let to login
        if (User::isIpAddressBlocked()) {
            $this->setError("username", Translation::getTranslation("ip_blocked"));
        }
        $userClass = ConfigurationManager::getInstance()->getEntityInfo("users")["class"];
        $this->user = $userClass::getUserByUsername($this->request["username"]) ?:
                    $userClass::getUserByEmail($this->request["username"]);
        if ($this->user && !$this->user->active->getValue()) {
            $this->setError("username", Translation::getTranslation("account_blocked"));
        }

        //if login fails for more than 10 times block this ip
        if (isset($_SESSION[self::LOGIN_UNTRUSTED_ACTIONS]) && $_SESSION[self::LOGIN_UNTRUSTED_ACTIONS] > 10) {
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
        if (
            empty($this->errors) &&
            (!$this->user || !password_verify($this->request["password"], $this->user->password))
        ) {
            if (isset($_SESSION[self::LOGIN_UNTRUSTED_ACTIONS])) {
                $_SESSION[self::LOGIN_UNTRUSTED_ACTIONS]++;
                if ($_SESSION[self::LOGIN_UNTRUSTED_ACTIONS] > 3) {
                    $this->setError("password", Translation::getTranslation("too_many_login_fails"));
                }
            } else {
                $_SESSION[self::LOGIN_UNTRUSTED_ACTIONS] = 1;
            }
            $this->setError("password", Translation::getTranslation("wrong_credidental"));
        }

        if (!empty($this->errors)) {
            //Logging failed login actions
            $login_log = new Logins();
            $login_log->ip_address->setValue(User::getUserIp());
            $login_log->username->setValue($_POST["username"]);
            $login_log->save();
            return false;
        }

        return true;
    }

    public function submit()
    {
        //login successful
        $this->user->last_access->setValue(\CoreDB::currentDate());
        $this->user->save();
        $_SESSION[BASE_URL . "-UID"] = $this->user->ID;
        if (isset($_POST["remember-me"]) && $_POST["remember-me"]) {
            $jwt = new JWT();
            $payload = new stdClass();
            $payload->ID = $this->user->ID->getValue();
            $jwt->setPayload($payload);
            setcookie(
                "session-token",
                $jwt->createToken(),
                strtotime("+1 week"),
                SITE_ROOT ?: "/",
                \CoreDB::baseHost(),
                $_SERVER['SERVER_PORT'] == 443
            );
        }

        Watchdog::log("login", $this->user->username);

        unset($_SESSION[self::PASSWORD_FALSE_COUNT]);
        unset($_SESSION[self::LOGIN_UNTRUSTED_ACTIONS]);

        //Clearing failed login actions
        CoreDB::database()->delete(Logins::getTableName())
            ->condition("username", $this->user->username)
            ->execute();
        if (isset($_GET["destination"])) {
            \CoreDB::goTo(BASE_URL . $_GET["destination"]);
        } elseif ($this->user->isAdmin()) {
            \CoreDB::goTo(AdminController::getUrl());
        } else {
            \CoreDB::goTo(BASE_URL);
        }
    }

    protected function csrfTokenCheckFailed()
    {
        parent::csrfTokenCheckFailed();
        if (isset($_SESSION[self::LOGIN_UNTRUSTED_ACTIONS])) {
            $_SESSION[self::LOGIN_UNTRUSTED_ACTIONS]++;
        } else {
            $_SESSION[self::LOGIN_UNTRUSTED_ACTIONS] = 1;
        }
    }
}
