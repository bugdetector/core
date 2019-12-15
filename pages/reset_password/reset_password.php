<?php

class Reset_passwordController extends Page{
    
    const RESET_PASSWORD_ID = "RESET_PASSWORD_ID";
    const RESET_PASSWORD_USER = "RESET_PASSWORD_USER";
    
    public function check_access() : bool {
        return !get_current_core_user()->isLoggedIn();
    }

    protected function echoContent() {
        if(!$_GET){
            Utils::create_warning_message(_t(87), "alert-danger");
        }
        if(isset($_GET["USER"]) && isset($_GET["KEY"]) ){
            $query = db_select(RESET_PASSWORD_QUEUE)
                ->condition("`USER` = :USER AND `KEY` = :KEY", $_GET);
            $reset_password_queue = $query->execute()->fetch(PDO::FETCH_ASSOC);
            if(!$reset_password_queue){
                Utils::create_warning_message(_t(87), "alert-danger");
                session_destroy();
            }else {
                $_SESSION[self::RESET_PASSWORD_ID] = $reset_password_queue["ID"];
                $_SESSION[self::RESET_PASSWORD_USER] = $_GET["USER"];
            }
        }
        if(isset($_POST["PASSWORD"]) && isset($_POST["PASSWORD2"])){
            if ($_POST["PASSWORD"] != $_POST["PASSWORD2"] || !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\p{P})[a-zA-Z\d\p{P}]{8,}$/", $_POST["PASSWORD"]) ) {
                Utils::create_warning_message(_t(47), "alert-info");
            } else {
                $user = User::getUserById($_SESSION[self::RESET_PASSWORD_USER]);
                $user->PASSWORD = hash("SHA256", $_POST["PASSWORD"]);
                $user->update();
                
                $reset_password_queue = new DBObject("RESET_PASSWORD_QUEUE");
                $reset_password_queue->getById($_SESSION[self::RESET_PASSWORD_ID]);
                $reset_password_queue->delete();
                
                $message = _t(86);
                $username = $user->NAME." ".$user->SURNAME;
                
                Utils::HTMLMail($user->EMAIL, _t(73), $message, $username);
                
                Utils::create_warning_message(_t(86), "alert-success");
                echo "<a href='".SITE_ROOT."/' class='btn btn-primary'>". _t(64)."</a>";
                
                return;
            }
            
        }
        require 'reset_password_html.php';
        echo_reset_password_page($this);
    }

}

