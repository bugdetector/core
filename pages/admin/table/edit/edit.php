<?php
class AdminTableEditController extends AdminTableController{
    
    const FORM_ID = "edit_table";

    private $request_table;
    private $table_comment;
    
    protected function preprocessPage() {
        if(isset($_POST["save_table"])){
            if(!$this->checkCsrfToken(self::FORM_ID)){
                $this->create_warning_message(_t("invalid_operation"));
            } else {
                $table_name =  preg_replace("/[^a-z1-9_]+/", "", $_POST["table_name"]);
                $table_comment = htmlspecialchars($_POST["table_comment"]);
                $fields = $_POST["fields"];
                if(in_array($table_name, CoreDB::get_information_scheme())){
                    $this->create_warning_message(_t("table_exits"));
                }else{
                    try {
                        db_create($table_name)->setFields($fields)->setComment($table_comment)->execute();
                        db_truncate(Cache::TABLE);
                    } catch (Exception $ex) {
                        $this->create_warning_message($ex->getMessage());
                    }
                    $this->create_warning_message(_t("table_create_success"), "alert-success");
                    Utils::core_go_to(BASE_URL."/admin/table/edit/$table_name");
                }
            }
        }else if(isset($_POST["alter_table"])){
            if(!$this->checkCsrfToken(self::FORM_ID)){
                $this->create_warning_message(_t("invalid_operation"));
            } else {
                $tablename = $this->arguments[0];
                $fields = $_POST["fields"];
                $db = CoreDB::getInstance();
                try{
                    $db->beginTransaction();
                    foreach ($fields as $field) {
                        db_alter($tablename)->addField($field)->execute();
                        db_truncate(Cache::TABLE);
                    }
                    $db->commit();
                    $this->create_warning_message(_t(32), "alert-success");
                } catch (Exception $ex){
                    $this->create_warning_message($ex->getMessage());
                }
                Utils::core_go_to(BASE_URL."/admin/table/edit/$table_name");
            }
        }
        if(isset($this->arguments[0]) && in_array($this->arguments[0], CoreDB::get_information_scheme())){
            $this->request_table = $this->arguments[0];
            $this->table_comment = CoreDB::getTableComment($this->request_table);
            $this->setTitle(_t("edit_table")." | {$this->request_table}");
        }else if(!isset($this->arguments[0])){
            $this->setTitle(_t("new_table"));
        }else{
            Utils::core_go_to(BASE_URL."/admin/table/edit");
        }

        $this->form_build_id = $this->createCsrf(self::FORM_ID);
        $this->form_token = $this->createFormToken($this->form_build_id);
        $this->add_js_files("pages/admin/table/edit/edit.js");
    }

    public function echoContent() {
        $this->create_warning_message(_t("available_characters", ["a-z, _, 1-9"]), "alert-info");
        require 'edit_html.php';
    }

    protected function add_default_translations()
    {
        parent::add_default_translations();
        $this->add_frontend_translation("length_varchar");
        $this->add_frontend_translation("reference_table");
        $this->add_frontend_translation("field_drop_accept");
        $this->add_frontend_translation("check_wrong_fields");
    }

}