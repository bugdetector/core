<?php

class LogoutController extends Page{    
    
    
    public function check_access() : bool {
        return User::get_current_core_user()->isLoggedIn();
    }

    protected function preprocessPage(){
        session_destroy();
        setcookie("session-token", "");
        Utils::core_go_to(BASE_URL);
    }


    protected function echoContent() {
    }
}