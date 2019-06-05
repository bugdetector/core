<?php

class NewTableController extends TableController{
    
    const FORM = "new_table";

    public $request_table;
    public $form_build_id;

    public function echoContent() {
        if(isset($this->arguments[2]) && in_array($this->arguments[2], get_information_scheme())){
            //$this->arguments[2] = table name
            $this->form_build_id = create_csrf(self::FORM, $this->arguments[2]);
            $this->request_table = $this->arguments[2];
        }else{
            $this->form_build_id = create_csrf(self::FORM, "new");
        }
        create_warning_message(_t(56, ["a-z, _, 1-9"]), "alert-info");
        require 'new_html.php';
        echo_create_table_page($this);
    }

}