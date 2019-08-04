<?php
/*
 * $unrestricted_pages = [
            "login",
            "forget_password"
        ];
        $page = isset(Router::getInstance()->get_arguments()[0]) ? Router::getInstance()->get_arguments()[0] : "mainpage";
        if(in_array($page , $unrestricted_pages)){
            NOEXPR;
        }elseif(!get_current_core_user()->isAdmin() && !in_array($page, $unrestricted_pages)){
            core_go_to(BASE_URL."/login");
        }elseif(!in_array($page, scandir("pages/admin/pages"))){
            $page = Router::$notFound;
        }
        require __DIR__."/pages/$page/$page.php";
        $pageControllerName = $page."Controller";
        $this->current_page = new $pageControllerName($this->arguments);
        $this->current_page->preprocessPage();
        $this->add_js_files("js/core.js");
        if(get_current_core_user()->isAdmin()){
            $this->add_js_files("js/core-admin.js");
        }
 */
class AdminController extends Page {
    
    private $subpage;
    
    const admin_mainpage = "mainpage";
    public function __construct(array $arguments) {
        parent::__construct($arguments);
        $page = isset($arguments[0]) ? $arguments[0] : self::admin_mainpage;
        if(!is_subclass_of($this, AdminController::class) && is_dir(__DIR__."/pages/".$page)){
            require __DIR__."/pages/".$page."/".$page.".php";
            $pageControllerName = $page."Controller";
            $this->subpage = new $pageControllerName($arguments);
            if(is_subclass_of($this->subpage, ServicePage::class)){
                $this->subpage->echoPage();
                die();
            }
        }
        
        
    }
    
    public function check_access() : bool {
        return get_current_core_user()->isAdmin();
    }
    
    protected function preprocessPage() {
        parent::preprocessPage();
        if($this->subpage){
            $this->subpage->preprocessPage();
        }
        if(get_current_core_user()->isAdmin()){
            $this->add_js_file("js/core-admin.js");
        }
    }
    
    protected function echoContent() {
        if($this->subpage){
            $this->subpage->echoContent();
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


