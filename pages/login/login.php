<?php

class LoginController extends Page{
    
    const FORM_ID = "login_form";
    

    public $form_build_id;
    
    public function __construct($arguments) {
        parent::__construct($arguments);
    }

    public function check_access() : bool {
        return TRUE;
    }
    protected function preprocessPage(){
        $userip = isset($_POST["form_build_id"]) ? FormBuilder::get_csrf($_POST["form_build_id"], self::FORM_ID) : User::get_user_ip();
        if($userip != User::get_user_ip()){
            if(isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS])){
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS]++;
            }else{
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS] = 1;
            }
        }
        try{
            if(isset($_POST["login"])){
                $user = User::login($_POST["username"], $_POST["password"]);    
            }
        } catch (Exception $ex){
            //Logging failed login actions
            $login_log = new DBObject(LOGINS);
            $login_log->IP_ADDRESS = User::get_user_ip();
            $login_log->USERNAME = $_POST["username"];
            $login_log->DATE = Utils::get_current_date();
            $login_log->insert();

            http_response_code(400);
            $this->create_warning_message($ex->getMessage());
            return;
        }
        if(isset($user)){
            if($user->isLoggedIn()){
                //Clearing failed login actions
                db_delete(LOGINS)->condition("USERNAME = :username", [":username" =>$user->USERNAME])->execute();
            }
            if($user->isLoggedIn() && $user->isAdmin() ){
                Utils::core_go_to(SITE_ROOT."/admin");
            }else{
                Utils::core_go_to(SITE_ROOT);
            }
        }
    }


    protected function echoContent() {
        $this->form_build_id = FormBuilder::create_csrf(self::FORM_ID , User::get_user_ip());
        require 'login_html.php';
        echo_login_page($this);
    }
    
    public function echoNavbar(){}
}