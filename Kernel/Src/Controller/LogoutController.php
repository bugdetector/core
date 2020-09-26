<?php

namespace Src\Controller;

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
        setcookie("session-token", "", 0, SITE_ROOT, \CoreDB::baseHost(), false, true);
        \CoreDB::goTo(BASE_URL);
    }


    public function echoContent()
    {
    }
}
