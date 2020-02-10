<?php

class AdminAjaxController extends ServicePage{
    
    public function callService(string $service_name) {
        $this->$service_name();
    }
    
    public function check_access() : bool {
        return User::get_current_core_user()->isAdmin();
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
             $object->map($values);
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
     * Returns foreign key entry
     */
    private function get_fk_entry() {
        $description = get_foreign_key_description($_POST["table"], $_POST["column"])->fetch(PDO::FETCH_NUM);
        $object = new DBObject($description[0]);
        $object->getById(intval($_POST["fk"]));
        $return_string = "";
        foreach ( $object->toArray() as $key => $field){
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
            db_drop($tablename)->execute();
            echo json_encode(["status" => "true", "message" => _t(69, [$tablename])]);
        }
    }

    /**
     * Drops table or field
     */
    private function dropfield(){
        $tablename = $_POST["tablename"];
        $column = $_POST["column"];
        if(in_array($tablename, get_information_scheme())){
            db_drop($tablename)->setColumn($column)->execute();
            echo json_encode(["status" => "true", "message" => _t(119, [$column])]);
        }
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
            $this->send_result(_t(70, [$user_to_delete->USERNAME]));
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
           Translator::import_translations();
            $this->send_result(_t(107));
        } catch (Exception $ex) {
            $this->throw_exception_as_json($ex->getMessage());
        }
    }
    
    private function langexp() {
        try{
            Translator::export_translations();
            $this->send_result(_t(106));
        } catch (Exception $ex) {
            $this->throw_exception_as_json($ex->getMessage());
        }
    }
}