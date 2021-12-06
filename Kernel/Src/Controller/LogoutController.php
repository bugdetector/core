<?php

namespace Src\Controller;

use Src\BaseTheme\BaseTheme;
use Src\Entity\Session;

class LogoutController extends BaseTheme
{
    
    
    public function checkAccess(): bool
    {
        return \CoreDB::currentUser()->isLoggedIn();
    }

    public function preprocessPage()
    {
        if (isset($_SESSION[BASE_URL . "-BACKUP-UID"])) {
            $_SESSION[BASE_URL . "-UID"] = $_SESSION[BASE_URL . "-BACKUP-UID"];
            unset($_SESSION[BASE_URL . "-BACKUP-UID"]);
        } else {
            $session = Session::get(["session_key" => session_id()]);
            if ($session) {
                $session->delete();
            }
            session_destroy();
            setcookie(
                "session-token",
                "",
                0,
                SITE_ROOT ?: "/",
                \CoreDB::baseHost(),
                $_SERVER['SERVER_PORT'] == 443
            );
        }
        \CoreDB::goTo(BASE_URL);
    }


    public function echoContent()
    {
    }
}
