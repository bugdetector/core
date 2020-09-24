<?php

namespace Src\Controller;

use Src\Entity\User;
use Src\BaseTheme\BaseTheme;

class LogoutController extends BaseTheme
{
    
    
    public function checkAccess() : bool
    {
        return \CoreDB::currentUser()->isLoggedIn();
    }

    public function preprocessPage()
    {
        session_destroy();
        setcookie("session-token", "");
        \CoreDB::goTo(MainpageController::getUrl());
    }


    public function echoContent()
    {
    }
}
