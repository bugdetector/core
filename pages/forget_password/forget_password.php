<?php

class Forget_passwordController extends Page{
    
    const FORM_ID = "forget_password";
    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-info"];
    }

    public function check_access() : bool {
        return !User::get_current_core_user()->isLoggedIn();
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

    protected function preprocessPage()
    {
        if( isset($_POST["reset"]) && $this->checkCsrfToken(self::FORM_ID) ){
            /**
             * @var User $user
             */
            $user = User::get(["username" => $_POST["username"], "email" => $_POST["email"]]);
            if(!$user){
                $this->create_warning_message (_t("wrong_username_or_email"));
            } else {
                $reset_password = new DBObject("reset_password_queue");
                /**
                 * @var ResetPassword $reset_password
                 */
                $reset_password = ResetPassword::get(["user" => $user->ID]);
                if( !$reset_password){
                    $reset_password = new ResetPassword();
                    $reset_password->user = $user->ID;
                    $reset_password->key = hash("SHA256", Utils::get_current_date().json_encode($user));
                    $reset_password->save();
                }
                
                $reset_link = BASE_URL."/reset_password/?USER=".$user->ID."&KEY=".$reset_password->key;
                $message = _t_email("password_reset" ,[$reset_link, $reset_link]);
                $username = $user->getFullName();
                
                Utils::HTMLMail($user->email, _t("reset_password"), $message, $username);
                
                $this->create_warning_message(_t("password_reset_mail_success"), "alert-success");
            }
        }
        $this->form_build_id = $this->createCsrf(self::FORM_ID, User::get_user_ip());
        $this->form_token = $this->createFormToken($this->form_build_id);
    }

    protected function echoContent() {
        require 'forget_password_html.php';
    }

}

