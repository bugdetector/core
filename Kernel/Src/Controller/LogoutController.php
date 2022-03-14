<?php

namespace Src\Controller;

use CoreDB\Kernel\BaseController;

class LogoutController extends BaseController
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
