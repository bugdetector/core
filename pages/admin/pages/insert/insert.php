<?php

class InsertController extends AdminPage{

    public $object = NULL;
    public $table;

    public function __construct(array $arguments){
        parent::__construct($arguments);
        if(!$this->arguments){
            Router::getInstance()->loadPage(Router::$notFound);
        }
    }
    
    protected function preprocessPage() {
        parent::preprocessPage();
        if( !isset($this->arguments[1]) && !in_array($this->arguments[1], get_information_scheme())){
            create_warning_message(_t(67));
            return;
        }
        $this->table = $this->arguments[1];
        if (isset($_POST["insert?"])) {
            $this->insertRecord();
        }elseif(isset($this->arguments[2]) && is_numeric($this->arguments[2])){
            $this->object = new DBObject($this->arguments[1]);
            $this->object->getById(intval($this->arguments[2]));
            if($this->object->ID == 0){
                create_warning_message(_t(67));
                return;
            }
            if(isset($_POST["update?"])){
                $this->updateRecord();
            }elseif(isset($_POST["delete?"])){
                $this->object->delete();
                create_warning_message(_t(65),"alert-success");
                $this->object = NULL;
            }
        }
    }
    
    protected function echoContent() {
        require 'insert_html.php';
        echo_insert_page($this);
    }
    
    private function insertRecord(){
        $this->object = new DBObject($this->table);
        unset($_POST["insert?"]);
        object_map($this->object, $_POST);
        try {
            CoreDB::getInstance()->beginTransaction();
            $this->object->insert();
            unset($_FILES["files"]);
            if(!empty($_FILES)){
                include_files_for_object($this->object);
            }
            CoreDB::getInstance()->commit();
            create_warning_message(_t(91),"alert-success");
            core_go_to(BASE_URL."/admin/insert/{$this->table}/".$this->object->ID);
        } catch (PDOException $ex) {
            create_warning_message($ex->getMessage());
        }
    }
    
    private function updateRecord(){
        $values = $_POST;
        unset($values["update?"]);
        try {
            CoreDB::getInstance()->beginTransaction();
            object_map($this->object, $values);
            unset($_FILES["files"]);
            if(!empty($_FILES)){
               control_real_object_with_params($this->object->table, $this->object->ID);
               include_files_for_object($this->object);
            }else {
               $this->object->update();
            }
            CoreDB::getInstance()->commit();
            create_warning_message(_t(32), "alert-success");
        } catch (PDOException $ex) {
            create_warning_message($ex->getMessage());
        }
    }

}