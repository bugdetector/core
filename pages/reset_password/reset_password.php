<?php

class Reset_passwordController extends Page{
    
    public $is_passwords_not_matching = FALSE;
    public $is_params_not_matching = FALSE;
    
    const RESET_PASSWORD_ID = "RESET_PASSWORD_ID";
    const RESET_PASSWORD_USER = "RESET_PASSWORD_USER";

    public function echoPage() {
        $this->echoHeader();
        $this->echoContent();
    }

    protected function echoContent() {
        if(!$_GET){
            $this->is_params_not_matching = TRUE;
        }
        if(isset($_GET["USER"]) && isset($_GET["KEY"]) ){
            $query = db_select(RESET_PASSWORD_QUEUE)
                ->condition("`USER` = :USER AND `KEY` = :KEY", $_GET);
            $reset_password_queue = $query->execute()->fetch(PDO::FETCH_ASSOC);
            if(!$reset_password_queue){
                $this->is_params_not_matching = TRUE;
                session_destroy();
            }else {
                $_SESSION[self::RESET_PASSWORD_ID] = $reset_password_queue["ID"];
                $_SESSION[self::RESET_PASSWORD_USER] = $_GET["USER"];
            }
        }
        if(isset($_POST["PASSWORD"]) && isset($_POST["PASSWORD2"])){
            if ($_POST["PASSWORD"] != $_POST["PASSWORD2"] || !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", $_POST["PASSWORD"]) ) {
                $this->is_passwords_not_matching = TRUE;
            } else {
                $user = User::getUserById($_SESSION[self::RESET_PASSWORD_USER]);
                $user->PASSWORD = hash("SHA256", $_POST["PASSWORD"]);
                $user->update();
                
                $reset_password_queue = new DBObject("RESET_PASSWORD_QUEUE");
                $reset_password_queue->getById($_SESSION[self::RESET_PASSWORD_ID]);
                $reset_password_queue->delete();
                
                $message = _t(86);
                $username = $user->NAME." ".$user->SURNAME;
                
                HTMLMail($user->EMAIL, _t(73), $message, $username);
                
                create_warning_message(_t(86), "alert-success");
                echo "<a href='".SITE_ROOT."/' class='btn btn-primary'>". _t(64)."</a>";
                
                return;
            }
            
        }
        require 'reset_password_html.php';
        echo_reset_password_page($this);
    }

}

