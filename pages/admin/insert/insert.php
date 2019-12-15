<?php

class AdminInsertController extends AdminController{
    
    const FORM_ID = "insert_form";

    public $object = NULL;
    public $table;
    public $form;
    
    public function __construct(array $arguments){
        parent::__construct($arguments);
        if(!$this->arguments){
            Router::getInstance()->loadPage(Router::$notFound);
        }
    }
    
    protected function preprocessPage() {
        parent::preprocessPage();
        if( !isset($this->arguments[0]) && !in_array($this->arguments[0], get_information_scheme())){
            Utils::create_warning_message(_t(67));
            return;
        }
        $this->table = $this->arguments[0];
        $this->object = new DBObject($this->table);
        if (isset($_POST["insert?"])) {
            $this->checkCsrf() && $this->insertRecord();
        }elseif(isset($this->arguments[1]) && is_numeric($this->arguments[1])){
            $this->object->getById(intval($this->arguments[1]));
            if($this->object->ID == 0){
                Utils::create_warning_message(_t(67));
                return;
            }
            if(isset($_POST["update?"])){
                $this->checkCsrf() && $this->updateRecord();
            }elseif(isset($_POST["delete?"])){
                $this->checkCsrf() && $this->object->delete();
                Utils::create_warning_message(_t(65),"alert-success");
                $this->object = new DBObject($this->arguments[0]);
            }
        }
    }
    
    protected function echoContent() {
        $this->form = $this->object->getForm("object");
        $this->form->addField(
                (new InputField("form-build-id"))
                ->setValue(create_csrf(self::FORM_ID, $this->table))
                ->addClass("hidden")
               );
        require 'insert_html.php';
    }
    
    private function insertRecord(){
        $this->object = new DBObject($this->table);
        unset($_POST["insert?"]);
        object_map($this->object, $_POST["object"]);
        try {
            CoreDB::getInstance()->beginTransaction();
            $this->object->insert();
            unset($_FILES["files"]);
            if(!empty($_FILES)){
                $this->object->include_files($_FILES["object"]);
            }
            CoreDB::getInstance()->commit();
            Utils::create_warning_message(_t(91),"alert-success");
            core_go_to(BASE_URL."/admin/insert/{$this->table}/".$this->object->ID);
        } catch (PDOException $ex) {
            Utils::create_warning_message($ex->getMessage());
        }
    }
    
    private function updateRecord(){
        unset($_POST["update?"]);
        try {
            CoreDB::getInstance()->beginTransaction();
            object_map($this->object, $_POST["object"]);
            unset($_FILES["files"]);
            if(!empty($_FILES)){
               $this->object->include_files($_FILES["object"]);
            }else {
               $this->object->update();
            }
            CoreDB::getInstance()->commit();
            Utils::create_warning_message(_t(32), "alert-success");
        } catch (PDOException $ex) {
            Utils::create_warning_message($ex->getMessage());
        }
    }
    
    private function checkCsrf(){
        if(get_csrf($_POST["form-build-id"], self::FORM_ID) != $this->table){
            Utils::create_warning_message(_t(67));
            return FALSE;
        }
        return TRUE;
    }
}