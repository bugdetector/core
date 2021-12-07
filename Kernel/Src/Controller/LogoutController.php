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
        \CoreDB::userLogout();
        \CoreDB::goTo(BASE_URL);
    }


    public function echoContent()
    {
    }
}
