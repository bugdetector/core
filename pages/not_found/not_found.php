<?php

class Not_foundController extends Page {
    
    public function check_access() : bool{
        return TRUE;
    }
    
    protected function preprocessPage() {
        $this->setTitle(_t(89).": "._t(90));
    }
    
    
    protected function echoContent() {
        http_response_code(404);
        require 'not_found_html.php';
    }

}