<?php

class DBObject{
    public $table;
    protected $changed_fields = [];

    public $ID;
    public function __construct(string $table) {
        $this->table = $table;
    }

    public function getById(int $id){
        $result = db_select($this->table)->condition("ID = :id")->params(["id" => $id])->execute()->fetch(PDO::FETCH_ASSOC);
        if(is_array($result)){
            $this->map($result);
        }
    }

    /**
    * Set fields of object using an array with same keys
    * @param DBObject $object
    * @return \array
    */
    public function map(array $array){
        $object_class_name = get_class($this);
        $this->changed_fields = [];
        foreach ($array as $key => $value){
            if($object_class_name != "DBObject" && !property_exists($this,$key)){
                continue;
            }
            if(get_field_from_object($this, $key) != $value){
                $this->changed_fields[$key] = [
                    "old_value" => get_field_from_object($this, $key),
                    "new_value" => $value
                ];
            }
            $this->$key = $value;
        }
    }

    /**
     * Converts an object to array including private fields
     * @param DBObject $object
     * @return \array
     */
    public function toArray() : array{
        $object_as_array = get_object_vars($this);
        unset($object_as_array["ID"]);
        unset($object_as_array["table"]);
        unset($object_as_array["created_at"]);
        unset($object_as_array["last_updated"]);
        unset($object_as_array["changed_fields"]);
        return $object_as_array;
    }

    public static function get(array $filter, string $table){
        $condition_sentence = "";
        $params = [];
        foreach($filter as $key => $value){
            $condition_sentence.= (!$condition_sentence ? "" : "AND")." `$key` = :$key ";
            $params[":$key"] = $value;
        }
        return db_select($table)->condition($condition_sentence)->params($params)->orderBy("ID")->execute()
        ->fetchObject(get_called_class(), [$table]) ? : null;
    }

    public static function getAll(array $filter, string $table){
        $condition_sentence = "";
        $params = [];
        foreach($filter as $key => $value){
            $condition_sentence.= (!$condition_sentence ? "" : "AND")." `$key` = :$key";
            $params[":$key"] = $value;
        }
        return db_select($table)->condition($condition_sentence)->params($params)->orderBy("ID")->execute()
        ->fetchAll(PDO::FETCH_CLASS, get_called_class(), [$table]);
    }

    protected function insert(){
        $statement = db_insert($this->table, $this->toArray())->execute();
        $this->ID = CoreDB::getInstance()->lastInsertId();
        return $statement;
    }
    
    protected function update(){
        return db_update($this->table, $this->toArray())->condition("ID = :id", ["id" => $this->ID])->execute();
    }
    public function save(){
        if($this->ID){
            return $this->update();
        }else{
            return $this->insert();
        }
    }
    
    public function delete(){
        if(!$this->ID){
            return FALSE;
        }
        $table_description = CoreDB::get_table_description($this->table);
        foreach ($table_description as $field) {
            if($field["Type"] == "tinytext"){
                $field_name = $field["Field"];
                Utils::remove_uploaded_file($this->table, $field_name, $this->$field_name);
            }
        }
        return db_delete($this->table)->condition(" ID = :id ", ["id" => $this->ID])->execute();
    }

    public function get_file_url_for_field($field_name){
        return BASE_URL."/files/uploaded/$this->table/$field_name/".$this->$field_name;
    }
    
    public function getForm(string $name = "") : FormBuilder {
        $form = new FormBuilder("POST");
        $form->setEnctype("multipart/form-data");
        $descriptions = CoreDB::get_table_description($this->table);
        
        $row = new Group("row");
        foreach ($descriptions as $index => $description){
            list($field, $wrapper_class) = $this->getFieldInput($description);
            $col = new Group($wrapper_class);
            if($name){
                $field->setName($name."[{$description["Field"]}]");
            }
            $col->addField($field);
            $row->addField($col);
        }
        $form->addField($row);
        $form->addField($this->getSubmitSection());
        return $form;
    }
    protected function getFieldInput($description) {
        list($input, $wrapper_class) = CoreDB::get_supported_data_types()[$this->get_input_type($description["Type"], $description["Key"])]["input_field_callback"]($this, $description, $this->table);
        if($description["Field"] === "ID"){
            $input->addAttribute("disabled", TRUE);
        }
        return [$input, $wrapper_class];
    }
    
    protected function getSubmitSection() {
        $submit_section = new Group("row");
        if($this->ID){
            $col = new Group("col-lg-3 col-md-4 col-sm-6");
            $update_button = new InputField("update?");
            $update_button->setType("submit")->addClass("btn btn-warning")->setValue(_t("update"));
            $delete_button = new InputField("");
            $delete_button->setType("button")->addClass("recordelete btn btn-danger")->setValue(_t("delete"));
            $hidden_delete_submit = new InputField("delete?");
            $hidden_delete_submit->setType("submit")->addClass("d-none");
            $col->addField($update_button)->addField($delete_button)->addField($hidden_delete_submit);
            $submit_section->addField($col);
            
        }
        
        $col = new Group("col-lg-3 col-md-4 col-sm-6");
        $insert_button = new InputField("insert?");
        $insert_button->setType("submit")->addClass("btn btn-primary")->setValue(_t("add"));
        $col->addField($insert_button);
        if(!$this->ID){
            $reset = new InputField("");
            $reset->setType("reset")->addClass("btn btn-danger")->setValue(_t("clean"));
            $col->addField($reset);
        }
        $submit_section->addField($col);
        return $submit_section;
    }


    protected function get_input_type(string $dataType, $key = ""){
        if($key == "MUL"){
            return $key;
        }elseif(strpos($dataType, "int") === 0){
            return "INT";
        }elseif (strpos($dataType, "varchar") === 0) {
            return "VARCHAR";
        }elseif(strpos($dataType,"datetime")===0){
            return "DATETIME";
        }else {
            return strtoupper($dataType);
        }
    }
    
    function include_files($from = NULL) {
        foreach (Utils::normalizeFiles($from) as $file_key => $file){
            if($file["size"] != 0){
                $file["name"] = $this->ID."_".filter_var($file["name"], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
                $this->$file_key = $file["name"];
                Utils::remove_uploaded_file($this->table, $file_key, $this->$file_key);
                if(!Utils::store_uploaded_file($this->table, $file_key, $file)){
                    CoreDB::getInstance()->rollback();
                    throw new Exception(_t(99));
                }
            }
        }
        $this->update();
    }
}


