<?php
class LoginController extends Page{
    
    const FORM_ID = "login_form";
    
    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-success"];
    }

    public function echoPage(){
        $this->add_default_js_files();
        $this->add_default_css_files();
        $this->add_default_translations();
        
        $this->preprocessPage();
        echo "<!DOCTYPE html>";
        echo "<html>";
        $this->echoHeader();
        echo '<body class="'.implode(" ", $this->body_classes).'">';
        $this->echoContent();
        echo '</body>';
        echo '</html>';
    }

    public function check_access() : bool {
        return TRUE;
    }
    protected function preprocessPage(){
        if(isset($_POST["login"]) && !$this->checkCsrfToken(self::FORM_ID)){
            if(isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS])){
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS]++;
            }else{
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS] = 1;
            }
        }else if(isset($_POST["login"])){
            try{
                $user = User::login($_POST["username"], $_POST["password"]);
                db_delete("logins")->condition("username = :username", [":username" =>$user->USERNAME])->execute();
            } catch (Exception $ex){
                //Logging failed login actions
                $login_log = new Logins();
                $login_log->ip_address = User::get_user_ip();
                $login_log->username = $_POST["username"];
                $login_log->save();

                http_response_code(400);
                $this->create_warning_message($ex->getMessage());
                return;
            }
        }
        if(User::get_current_core_user()->isLoggedIn()){
            //Clearing failed login actions
            if(isset($_GET["destination"])){
                Utils::core_go_to(SITE_ROOT.$_GET["destination"]);
            }elseif(User::get_current_core_user()->isAdmin() ){
                Utils::core_go_to(SITE_ROOT."/admin");
            }else{
                Utils::core_go_to(SITE_ROOT);
            }
        }
        $this->form_build_id = $this->createCsrf(self::FORM_ID);
        $this->form_token = $this->createFormToken($this->form_build_id);
    }


    protected function echoContent() {
        require 'login_html.php';
    }
    
}