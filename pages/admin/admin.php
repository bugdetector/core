<?php

class AdminController extends Page {
    
    private $subpage;
    
    const admin_mainpage = "mainpage";
    
    public function check_access() : bool {
        return get_current_core_user()->isAdmin();
    }
    
    protected function preprocessPage() {
        parent::preprocessPage();
        if(get_current_core_user()->isAdmin()){
            $this->add_js_files("js/core-admin.js");
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


