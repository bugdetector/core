<?php

class AdminAjaxController extends ServicePage{
    
    public function callService(string $service_name) {
        $this->$service_name();
    }
    
    public function check_access() : bool {
        return User::get_current_core_user()->isAdmin();
    }
    
    /**
     * Select from table
     */
    private function select(){
        if(in_array( $_POST["table"], get_information_scheme()) ){
            $columns = db_select($_POST["table"])->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
            $result = ["values" => $columns];
            $result["skeleton"] = get_table_description($_POST["table"]);
            echo json_encode($result);
        }
    }

     /**
     * Delete record
     */
    private function delete(){
        if(in_array( $_POST["table"], get_information_scheme()) ){
             $table = $_POST["table"];
             unset($_POST["table"]);
             $values = $_POST;
             $object = new DBObject($table);
             object_map($object, $values);
             $object->delete();
         }
    }
    
    /**
     * Returns table list
     */
    private function get_table_list(){
        echo json_encode(get_information_scheme());
    }
    
    /**
     * Returns table description
     */
    private function get_description(){
        echo json_encode(get_table_description($_POST["table"]));
    }
    
    /**
     * returns foreign key description
     */
    private function get_fk_description(){
        $table = $_POST["table"];
        if(in_array($table, get_information_scheme())) {
            $description = get_foreign_key_description($table, $_POST["key"])->fetch(PDO::FETCH_NUM);
            $keys = db_select($description[0])->select("", [$description[1]])->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
            $entry = db_select($description[0])->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
            echo json_encode(["status" => "true", "keys" => $keys, "entry" => $entry]);
        }
    }
    
    /**
     * Returns foreign key entry
     */
    private function get_fk_entry() {
        $description = get_foreign_key_description($_POST["table"], $_POST["column"])->fetch(PDO::FETCH_NUM);
        $object = new DBObject($description[0]);
        $object->getById(intval($_POST["fk"]));
        $return_string = "";
        foreach ( convert_object_to_array($object) as $key => $field){
            $return_string.= "$key = $field ";
        }
        echo $return_string;
    }
    
    /**
     * Returns field definition for new table definition
     */
    private function get_input_field(){
        require "pages/admin/views/field_definition.php";
        echo "<tr>";
        foreach (get_field_row($_POST["index"]) as $data){
            echo "<td>$data</td>";
        }
        echo "</tr>";
    }
    
    /**
     * Drops table
     */
    private function drop(){
        $tablename = $_POST["tablename"];
        if(in_array($tablename, get_information_scheme())){
            CoreDB::getInstance()->query("DROP TABLE `$tablename`");
            echo json_encode(["status" => "true", "message" => _t(69, [$tablename])]);
        }
        CoreDB::getInstance()->commit();
    }
    
    /**
     * Truncates table
     */
    private function truncate(){
        $tablename = $_POST["tablename"];
        if(in_array($tablename, get_information_scheme())){
            db_truncate($tablename);
            echo json_encode(["status" => "true", "message" => _t(110, [$tablename])]);
        }
        CoreDB::getInstance()->commit();
    }
    
    /**
     * Removes user
     */
    private function delete_user(){        
        $username = $_POST["USERNAME"];
        if($user_to_delete = User::getUserByUsername($username)){
            CoreDB::getInstance()->beginTransaction();
            $user_to_delete->delete();
            CoreDB::getInstance()->commit();
            $this->send_result(_t(70, $user_to_delete->USERNAME));
        }
    }
    
    /**
     * Removes role
     */
    private function remove_role(){
        $role = new DBObject(ROLES);
        $role->getById( User::getIdOfRole($_POST["ROLE"]));
        $role->ROLE != $_POST["ROLE"] ? $this->throw_exception_as_json(_t(67)) : "";
        $user = db_select(USERS_ROLES)->condition("ROLE_ID = :role_id", ["role_id" => $role->ID])->limit(1)->execute()->fetchAll(PDO::FETCH_NUM);
        if(count($user) > 0){
            $this->throw_exception_as_json(_t(71));
        }
        $role->delete();
        
        $this->send_result(_t(72));
    }
    
    private function langimp() {
        try{
            $translations = json_decode(file_get_contents(Translator::BACKUP_PATH));
            CoreDB::getInstance()->beginTransaction();
            db_truncate(TRANSLATIONS);
            foreach ($translations as $translation){
                    db_insert(TRANSLATIONS, (array) $translation)->execute();
            }
            $this->send_result(_t(107));
        } catch (Exception $ex) {
            $this->throw_exception_as_json($ex->getMessage());
        }
    }
    
    private function langexp() {
        try{
            $translations = db_select(TRANSLATIONS)->execute()->fetchAll(PDO::FETCH_ASSOC);
            if(file_exists(Translator::BACKUP_PATH)){
                unlink(Translator::BACKUP_PATH);
            }
            file_put_contents(Translator::BACKUP_PATH, json_encode($translations, JSON_PRETTY_PRINT));
            $this->send_result(_t(106));
        } catch (Exception $ex) {
            $this->throw_exception_as_json($ex->getMessage());
        }
    }
}