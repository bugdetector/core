<?php

class Access_DeniedController extends Page {
    
    public function check_access(): bool {
        return TRUE;
    }
    
     protected function preprocessPage()
    {
        $this->setTitle(_t("sorry").": "._t("access_denied"));
    }
    
    
    protected function echoContent() {
        http_response_code(403);
        require 'access_denied_html.php';
    }

}