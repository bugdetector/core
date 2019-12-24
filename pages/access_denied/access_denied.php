<?php

class AccessDeniedController extends Page {
    
    public function check_access(): bool {
        return TRUE;
    }
    
     protected function preprocessPage()
    {
        $this->setTitle(_t(89).": "._t(117));
    }
    
    
    protected function echoContent() {
        http_response_code(403);
        require 'access_denied_html.php';
    }

}