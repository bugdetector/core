<?php

class MainpageController extends SonbellekPage{
    
    public $results;
    public $form_build_id;
    const SEARCH_FORM = "search_form";

    protected function preprocessPage() {
        $this->form_build_id = create_csrf(self::SEARCH_FORM, self::SEARCH_FORM.get_user_ip());
        if(isset($_POST["search_action"]) && get_csrf($_POST["form_build_id"], self::SEARCH_FORM) == self::SEARCH_FORM.get_user_ip()){
            $this->results = Content::getContentList($_POST["search_param"]);
        } else {
            $this->results = Content::getContentList();
        }
        
        parent::preprocessPage();
    }

    protected function echoContent() {
        require 'mainpage_html.php';
        echo_mainpage($this);
    }
    
    public function import_view($view_name) {
        if(file_exists(__DIR__."/views/$view_name.php")){
            require "views/$view_name.php";
        } else {
            parent::import_view($view_name);
        }
    }
}