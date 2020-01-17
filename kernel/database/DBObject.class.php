<?php

class DBObject{
    public $table;
    
    public $ID;
    public function __construct(string $table) {
        $this->table = $table;
    }

    public function getById(int $id){
        $result = db_select($this->table)->condition("ID = :id")->params(["id" => $id])->execute()->fetch(PDO::FETCH_ASSOC);
        if(is_array($result)){
            object_map($this, $result);
        }
    }

    public static function get(array $filter, string $table){
        $condition_sentence = "";
        $params = [];
        foreach($filter as $key => $value){
            $condition_sentence.= (!$condition_sentence ? "" : "AND")." `$key` = :$key ";
            $params[":$key"] = $value;
        }
        return db_select($table)->condition($condition_sentence)->params($params)->orderBy("ID")->execute()
        ->fetchObject(get_called_class(), [$table]);
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

    public function insert(){
        $statement = db_insert($this->table, convert_object_to_array($this))->execute();
        $this->ID = CoreDB::getInstance()->lastInsertId();
        return $statement;
    }
    
    public function update(){
        return db_update($this->table, convert_object_to_array($this))->condition("ID = :id", ["id" => $this->ID])->execute();
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
        $table_description = get_table_description($this->table);
        foreach ($table_description as $field) {
            if($field[1] == "tinytext"){
                $field_name = $field[0];
                Utils::remove_uploaded_file($this->table, $field_name, $this->$field_name);
            }
        }
        return db_delete($this->table)->condition(" ID = :id ", ["id" => $this->ID])->execute();
    }

    protected function get_file_url_for_field($field_name){
        return BASE_URL."/files/uploaded/$this->table/$field_name/".$this->$field_name;
    }
    
    public function getForm(string $name = "") {
        $form = new FormBuilder("POST");
        $form->setEnctype("multipart/form-data");
        $descriptions = get_table_description($this->table);
        
        $row = new InputGroup("row");
        foreach ($descriptions as $index => $description){
            list($field, $wrapper_class) = $this->getFieldInput($description);
            $col = new InputGroup($wrapper_class);
            if($name){
                $field->setName($name."[$description[0]]");
            }
            $col->addField($field);
            $row->addField($col);
        }
        $form->addField($row);
        $form->addField($this->getSubmitSection());
        return $form;
    }
    protected function getFieldInput($description) {
        list($input, $wrapper_class) = get_supported_data_types()[$this->get_input_type($description[1], $description[3])]["input_field_callback"]($this, $description, $this->table);
        if($description[0] === "ID"){
            $input->addAttribute("disabled", TRUE);
        }
        return [$input, $wrapper_class];
    }
    
    protected function getSubmitSection() {
        $submit_section = new InputGroup("row");
        if($this->ID){
            $col = new InputGroup("col-lg-3 col-md-4 col-sm-6");
            $update_button = new InputField("update?");
            $update_button->setType("submit")->addClass("btn btn-warning")->setValue(_t(85));
            $delete_button = new InputField("");
            $delete_button->setType("button")->addClass("recordelete btn btn-danger")->setValue(_t(82));
            $hidden_delete_submit = new InputField("delete?");
            $hidden_delete_submit->setType("submit")->addClass("hidden");
            $col->addField($update_button)->addField($delete_button)->addField($hidden_delete_submit);
            $submit_section->addField($col);
            
        }
        
        $col = new InputGroup("col-lg-3 col-md-4 col-sm-6");
        $insert_button = new InputField("insert?");
        $insert_button->setType("submit")->addClass("btn btn-primary")->setValue(_t(14));
        $col->addField($insert_button);
        if(!$this->ID){
            $reset = new InputField("");
            $reset->setType("reset")->addClass("btn btn-danger")->setValue(_t(84));
            $col->addField($reset);
        }
        $submit_section->addField($col);
        return $submit_section;
    }


    private function get_input_type(string $dataType, $key = ""){
        if($key == "MUL"){
            return $key;
        }elseif(strpos($dataType, "int") === 0){
            return "INT";
        }elseif (strpos($dataType, "varchar") === 0) {
            return "VARCHAR";
        }else {
            return strtoupper($dataType);
        }
    }
    
    function include_files($from = NULL) {
        foreach (Utils::normalizeFiles($from) as $file_key => $file){
            if($file["size"] != 0){
                $file["name"] = $this->ID."_".filter_var($file["name"], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
                $this->$file_key = $file["name"];
                Utils::remove_uploaded_file($this->table, $file_key, $file);
                if(!Utils::store_uploaded_file($this->table, $file_key, $file)){
                    CoreDB::getInstance()->rollback();
                    throw new Exception(_t(99));
                }
            }
        }
        $this->update();
    }
}


