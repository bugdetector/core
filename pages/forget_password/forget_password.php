<?php

class Forget_passwordController extends Page{
    
    public function check_access() : bool {
        return !User::get_current_core_user()->isLoggedIn();
    }

    protected function echoContent() {
        if(isset($_POST["username"]) && isset($_POST["email"]) ){
            $user = db_select(User::TABLE)
                    ->condition("USERNAME = :username AND EMAIL = :email", ["username" => $_POST["username"], "email" => $_POST["email"]] )
                    ->execute()->fetch(PDO::FETCH_ASSOC);
            if(!$user){
                $this->create_warning_message (_t(74));
            } else {
                $reset_password = new DBObject(RESET_PASSWORD_QUEUE);
                
                $sended_key = db_select(RESET_PASSWORD_QUEUE)
                        ->condition("USER = :user_id",[":user_id" => $user["ID"]])
                        ->limit(1)
                        ->execute()->fetch(PDO::FETCH_ASSOC);
                if(isset($sended_key["ID"]) && $sended_key["ID"]){
                    $reset_password->map($sended_key);
                } else {
                    $reset_password->USER = intval($user["ID"]);
                    $reset_password->KEY = hash("SHA256", Utils::get_current_date().json_encode($user));
                    $reset_password->insert();
                }
                
                $email = $user["EMAIL"];
                $subject = _t(73);
                $reset_link = BASE_URL."/reset_password/?USER=".$user["ID"]."&KEY=".$reset_password->KEY;
                $message = _t_email("password_reset" ,[$reset_link, $reset_link]);
                $username = $user["NAME"]." ".$user["SURNAME"];
                
                Utils::HTMLMail($email, $subject, $message, $username);
                
                $this->create_warning_message(_t(88), "alert-success");
                return;
            }
        }
        require 'forget_password_html.php';
        echo_forget_password_page($this);
    }

}

