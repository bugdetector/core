<?php
class Reset_passwordController extends Page{
    
    const FORM_ID = "reset_password";
    private $user;
    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-info"];
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
        return !User::get_current_core_user()->isLoggedIn();
    }

    protected function preprocessPage()
    {
        if(!$_GET){
            $this->create_warning_message(_t("link_used"), "alert-danger");
        }
        if(isset($_GET["USER"]) && isset($_GET["KEY"]) ){
            $reset_password_queue = ResetPassword::get(["user" => $_GET["USER"], "key" => $_GET["KEY"]]);
            if(!$reset_password_queue){
                $this->create_warning_message(_t("link_used"), "alert-danger");
            }else{ 
                /**
                 * @var User $user
                 */
                $this->user = User::getUserById($_GET["USER"]);
                if(isset($_POST["reset"]) && $this->checkCsrfToken(self::FORM_ID) ){
                    if ($_POST["PASSWORD"] != $_POST["PASSWORD2"] || !User::validatePassword($_POST["PASSWORD"]) ) {
                        $this->create_warning_message(_t("password_validation_error"), "alert-info");
                    } else {
                        $this->user->password = hash("SHA256", $_POST["PASSWORD"]);
                        $this->user->save();
                        
                        $reset_password_queue = ResetPassword::get(["user" => $this->user->ID, "key" => $_GET["KEY"]]);
                        $reset_password_queue->delete();

                        $message = _t("password_reset_success");
                        $username = $this->user->getFullName();
                        
                        Utils::HTMLMail($this->user->email, _t("reset_password"), $message, $username);
                        
                        $this->create_warning_message(_t("password_reset_success"), "alert-success");
                        $this->reset_password_success = TRUE;
                    }
                    
                }
            }
        }
        $this->form_build_id = $this->createCsrf(self::FORM_ID, User::get_user_ip());
        $this->form_token = $this->createFormToken($this->form_build_id);
    }

    protected function echoContent() {
        require 'reset_password_html.php';
    }

}

