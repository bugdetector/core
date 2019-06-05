<?php

class MainpageController extends AdminPage{
    
    public $search_results = NULL;
    protected function echoContent() {
        if(isset($_POST["search_param"])){
            //$this->search_results = db_select()->execute()->fetchAll(PDO::FETCH_ASSOC);
        }
        require 'mainpage_html.php';
        echo_mainpage($this);
    }
    
    

}