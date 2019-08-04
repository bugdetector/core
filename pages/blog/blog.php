<?php

class BlogController extends SonbellekPage{
    public $url_alias;
    public $content;

    public function __construct(array $arguments) {
        $this->url_alias = $arguments[0];
    }
    
    protected function preprocessPage() {
        $content = Content::getByUrlAlias($this->url_alias);
        if(!$content->isExists()){
            Router::getInstance()->loadPage(Router::$notFound);
        } else {
            $this->content = $content;
        }
    }

    protected function echoContent() {
        $this->import_view("blog_sonbellek");
        echo_blog_page($this);
    }
    
    public function import_view($view_name) {
        if(file_exists(__DIR__."/views/$view_name.php")){
            require "views/$view_name.php";
        } else {
            parent::import_view($view_name);
        }
    }
}
