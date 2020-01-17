<?php

class AdminTableNewController extends AdminTableController{
    
    const FORM = "new_table";

    public $request_table;
    public $form_build_id;
    
    protected function preprocessPage() {
        parent::preprocessPage();
        $this->add_frontend_translation(118);
        if(isset($_POST["save_table"])){
            if(FormBuilder::get_csrf($_POST["form_build_id"], self::FORM) != ( isset($this->arguments[0]) ? $this->arguments[0]: "new")){
                $this->create_warning_message(_t(67));
            } else {
                $table_name =  preg_replace("/[^a-z1-9_]+/", "", $_POST["table_name"]);
                $fields = $_POST["fields"];
                if(in_array($table_name, get_information_scheme())){
                    $this->create_warning_message(_t(66));
                }else{
                    try {
                        db_create($table_name)->setFields($fields)->execute();
                    } catch (Exception $ex) {
                        $this->create_warning_message($ex->getMessage());
                    }
                    $this->create_warning_message(_t(68), "alert-success");
                    Utils::core_go_to(BASE_URL."/admin/table/new/$table_name");
                }
            }
        }else if(isset($_POST["alter_table"])){
            if(FormBuilder::get_csrf($_POST["form_build_id"], self::FORM) != $this->arguments[0]){
                $this->create_warning_message(_t(67));
            } else {
                $tablename = $this->arguments[0];
                $fields = $_POST["fields"];
                $db = CoreDB::getInstance();
                try{
                    $db->beginTransaction();
                    foreach ($fields as $field) {
                        db_alter($tablename)->addField($field)->execute();
                    }
                    $db->commit();
                    $this->create_warning_message(_t(32), "alert-success");
                } catch (Exception $ex){
                    $this->create_warning_message($ex->getMessage());
                }
            }
        }
        if(isset($this->arguments[0]) && in_array($this->arguments[0], get_information_scheme())){
            $this->form_build_id = FormBuilder::create_csrf(self::FORM, $this->arguments[0]);
            $this->request_table = $this->arguments[0];
        }else if(!isset($this->arguments[0])){
            $this->form_build_id = FormBuilder::create_csrf(self::FORM, "new");
        }else{
            Utils::core_go_to(BASE_URL."/admin/table/new");
        }
    }

    public function echoContent() {
        $this->create_warning_message(_t(56, ["a-z, _, 1-9"]), "alert-info");
        require 'new_html.php';
    }

}