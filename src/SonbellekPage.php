<?php

/**
 * Description of SonbellekPage
 *
 * @author Murat Baki Yücel
 */
abstract class SonbellekPage extends Page{
    
    abstract protected function echoContent();
    
    protected function add_default_css_files() {
        parent::add_default_css_files();
        $this->add_css_file("css/sonbellek.css");
    }
    
    protected function echoNavbar() {
        $this->import_view("navbar_sonbellek");
    }
}
