<?php

class AdminController extends Page {
    
    private $subpage;
    private $number_of_members;
    
    const admin_mainpage = "mainpage";
    
    public function check_access() : bool {
        return User::get_current_core_user()->isAdmin();
    }
    
    protected function preprocessPage() {
        parent::preprocessPage();
        if(User::get_current_core_user()->isAdmin()){
            $this->add_js_files("js/core-admin.js");
        }
        if(get_called_class() == self::class){
            $this->number_of_members = db_select(USERS)
            ->select_with_function(["COUNT(*) as count"])
            ->condition("USERNAME != :username", [":username" => "guest"])
            ->execute()->fetchObject()->count;
        }
    }
    
    protected function echoContent() {
        include 'admin_html.php';
    }
    public function echoNavbar() {
        $this->import_view("navbar_admin");
    }
    
    public function import_view($view_name) {
        if(file_exists(__DIR__."/views/$view_name.php")){
            require "views/$view_name.php";
        } else {
            parent::import_view($view_name);
        }
    }
    
    protected function echoTranslations() {
        $this->add_frontend_translation(79);
        $this->add_frontend_translation(80);
        $this->add_frontend_translation(81);
        $this->add_frontend_translation(62);
        $this->add_frontend_translation(63);
        $this->add_frontend_translation(82);
        $this->add_frontend_translation(83);
        
        parent::echoTranslations();
    }
}


