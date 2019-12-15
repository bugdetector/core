<?php

class LogoutController extends Page{    
    
    
    public function check_access() : bool {
        return get_current_core_user()->isLoggedIn();
    }

    protected function preprocessPage(){
        session_destroy();
        setcookie("session-token", "");
        core_go_to(BASE_URL);
    }


    protected function echoContent() {
    }
}