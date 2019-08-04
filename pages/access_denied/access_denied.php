<?php

class AccessDeniedController extends Page {
    
    public function check_access(): bool {
        return TRUE;
    }
    
    protected function echoContent() {
        http_response_code(403);
        require 'access_denied_html.php';
    }

}