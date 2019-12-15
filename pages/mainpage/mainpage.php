<?php

class MainpageController extends Page{
    
    public $results;
    public $form_build_id;
    const SEARCH_FORM = "search_form";

    public function check_access() : bool {
        return TRUE;
    }

    protected function echoContent() {
        require 'mainpage_html.php';
    }
}