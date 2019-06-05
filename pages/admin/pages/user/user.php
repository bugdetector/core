<?php

class UserController extends AdminPage{
    public $user;
    public $current_user_roles;
    public $excluded_user_roles;
    public $operation;
    public $form_build_id;
    
    protected function preprocessPage() {
        parent::preprocessPage();
        $this->operation = isset($_GET["q"]) ? $_GET["q"] : "update";
        if(isset($this->arguments[1])){
            $this->user = User::getUserByUsername($this->arguments[1]);
            if(!$this->user){
                create_warning_message(_t(19));
                return;
            }
        } else if($this->operation === "update") {
            $this->user = get_current_core_user();
        } else {
            $this->user = new User();
        }
        if(isset($_POST["save"]) || isset($_POST["change_password"])){
            try {
                $username = get_csrf($_POST["form_build_id"], "user_edit_form");
                if($this->operation != "add" && $username != $this->user->USERNAME){
                    create_warning_message(_t(67));
                    return;
                }
            } catch (Exception $ex) {
                create_warning_message($ex->getMessage());
                return;
            }
            $submitted = isset($_POST["change_password"]) ? "change_password" : (isset($_POST["save"]) ? "save" : "");
            switch ($submitted){
                case "save":
                    $user_info = $_POST["user_info"];
                    if(isset($user_info["USERNAME"]) && (preg_match("/[^a-z_\-0-9]+/i", $user_info["USERNAME"]) || strlen($user_info["USERNAME"]) < 4) ){
                        create_warning_message(_t(44, [4]));
                    }elseif (preg_match("/[^a-z\s\p{L}]+/iu", $user_info["NAME"]) ){
                        create_warning_message(_t(26, [mb_strtolower(_t(27))]));
                    }elseif (preg_match("/[^a-z\s\p{L}]+/iu", $user_info["SURNAME"]) ) {
                        create_warning_message(_t(26, [_t(28)]));
                    }elseif (preg_match("/[^0-9]+/i", $user_info["PHONE"]) || strlen($user_info["PHONE"]) != 10) {
                        create_warning_message(_t(26, [_t(29)]));
                    }elseif(filter_var($user_info["EMAIL"], FILTER_VALIDATE_EMAIL) == ""){
                        create_warning_message(_t(30));
                    }elseif (!isset ($user_info["ROLES"])) {
                        create_warning_message(_t(45));
                    }elseif(count(array_diff($user_info["ROLES"], User::getAllAvailableUserRoles() ) ) ){
                        create_warning_message(_t(31));
                    }else{
                        if($this->perform_operation($user_info)){
                            create_warning_message(_t(32), "alert-success");
                        }
                    }
                    break;
                case "change_password":
                    $password = $_POST["password"];
                    if ($password["PASSWORD"] != $password["PASSWORD2"] || 
                        !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\p{P})[a-zA-Z\d\p{P}]{8,}$/", $password["PASSWORD"]) || 
                        $this->user->PASSWORD != hash( "SHA256", $password["ORIGINAL_PASSWORD"]) ) {

                        create_warning_message(_t(46));
                        create_warning_message(_t(47), "alert-warning" );
                    }else{
                        $this->user->PASSWORD = hash("SHA256", $password["PASSWORD"]);
                        $this->user->update();
                        create_warning_message(_t(32), "alert-success");
                    }
                    break;
                default :
                    create_warning_message(_t(67));
                }
            }
        $this->current_user_roles = $this->user->getUserRoles();
        $this->excluded_roles = array_diff(User::getAllAvailableUserRoles(), $this->current_user_roles);
    }

    protected function echoContent() {
        $this->form_build_id = create_csrf("user_edit_form", $this->user->USERNAME);
        require 'user_html.php';
        echo_profile_page($this);
    }
    
    private function perform_operation($user_info) {
        $this->user->NAME = $user_info["NAME"];
        $this->user->SURNAME = $user_info["SURNAME"];
        $this->user->EMAIL = $user_info["EMAIL"];
        $this->user->PHONE = $user_info["PHONE"];
        $this->user->ROLES = $user_info["ROLES"];
        
        CoreDB::getInstance()->beginTransaction();
        
        $result = NULL;
        if($this->operation == "add"){
            $this->user->USERNAME = $user_info["USERNAME"];
            $result = $this->insertUser();
        }else{
            $result = $this->user->update();
        }
        if($result){
            $this->user->updateRoles($user_info["ROLES"]);
        }
        
        CoreDB::getInstance()->commit();
        
        return $result;
    }


    private function insertUser(): bool{
        if(!$this->user->checkUsernameInsertAvailable()){
            create_warning_message(_t(42));
            return FALSE;
        }elseif (!$this->user->checkEmailInsertAvailable()) {
            create_warning_message(_t(41));
            return FALSE;
        }
        $this->user->STATUS = User::STATUS_ACTIVE;
        if($this->user->insert()){
            $reset_password = new DBObject(RESET_PASSWORD_QUEUE);
            $reset_password->USER = intval($this->user->ID);
            $reset_password->KEY = hash("SHA256", get_current_date().json_encode($this->user));
            $reset_password->insert();

            $email = $this->user->EMAIL;
            $password_reset_link = BASE_URL."/reset_password/?USER=".$this->user->ID."&KEY=".$reset_password->KEY;
            $message = _t_email("user_insert", [SITE_NAME, $this->user->USERNAME, $password_reset_link, $password_reset_link ]);
            $username = $this->user->NAME." ".$this->user->SURNAME;
            $subject = _t(92, [SITE_NAME]);

            if(HTMLMail($email, $subject, $message, $username)){
                return TRUE;
            }else{
                CoreDB::getInstance()->rollback();
                create_warning_message(_t(43));
            }
        }
        return FALSE;
    }
    
    private function update(){
        if (!$this->user->checkEmailInsertAvailable()) {
            create_warning_message(_t(41));
            return FALSE;
        }
        return $this->user->update();
    }
}