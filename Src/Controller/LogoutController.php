<?php

namespace Src\Controller;


use Src\Entity\User;
use Src\Theme\BaseTheme\BaseTheme;

class LogoutController extends BaseTheme{    
    
    
    public function checkAccess() : bool {
        return User::get_current_core_user()->isLoggedIn();
    }

    public function preprocessPage(){
        session_destroy();
        setcookie("session-token", "");
        \CoreDB::goTo(BASE_URL);
    }


    public function echoContent() {}
}