<?php
class Not_foundController extends AdminPage {
    
    protected function echoContent() {
        http_response_code(404);
        require 'not_found_html.php';
    }

}