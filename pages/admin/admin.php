<?php
$unrestricted_pages = [
    "login",
    "forget_password"
];
$page = isset(Router::getInstance()->get_arguments()[0]) ? Router::getInstance()->get_arguments()[0] : "mainpage";
if(in_array($this->page , $unrestricted_pages)){
    NOEXPR;
}elseif(!get_current_core_user()->isAdmin() && !in_array($page, $unrestricted_pages)){
    core_go_to(BASE_URL."/login");
}elseif(!in_array($page, scandir("pages/admin/pages"))){
    $page = Router::$notFound;
}
require __DIR__."/pages/$page/$page.php";

abstract class AdminPage extends Page {
    
    public function __construct(array $arguments) {
        parent::__construct($arguments);
    }
    
    protected function preprocessPage() {
        $this->add_js_files("js/core.js");
        if(get_current_core_user()->isAdmin()){
            $this->add_js_files("js/core-admin.js");
        }
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


