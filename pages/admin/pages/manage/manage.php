<?php

class ManageController extends AdminController{
    public $operation;
    public $table_headers;
    public $table_content;
    public $page;
    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->operation = isset($this->arguments[1]) ? $this->arguments[1] : "";
    }
    
    protected function preprocessPage() {
        parent::preprocessPage();
        if(isset($_POST["ROLE"])){
            $role = new DBObject(ROLES);
            $role->ROLE = $_POST["ROLE"];
            $role->insert(); 
            create_warning_message(_t(78, [$role->ROLE]),"alert-success");
        }
        $this->add_frontend_translation(98);
        $this->add_frontend_translation(103);
        $this->add_frontend_translation(104);
        
        $this->page = isset($_GET["page"]) && $_GET["page"]>1 ? $_GET["page"] : 1;
        
        switch ($this->operation){
        case "user":
            $this->getUserTableInfo();
            break;
        case "role":
            $this->getRoleTableInfo();
            break;
        case "translation":
            $this->getTranslationInfo();
            break;
        }
    }

    protected function echoContent() {        
        require 'manage_html.php';
        echo_manage_page($this);
    }
    
    private function getUserTableInfo() {
        $this->table_content = db_select(USERS)->select(USERS, ["ID", "USERNAME","NAME","SURNAME","EMAIL","PHONE","CREATED_AT", "ACCESS"])
                ->orderBy("ID")
                ->limit(PAGE_SIZE_LIMIT, ($this->page-1)*PAGE_SIZE_LIMIT)
                ->execute()->fetchAll(PDO::FETCH_NUM);
        $this->table_headers = ["ID", _t(20), _t(27),mb_convert_case(_t(28),MB_CASE_TITLE), _t(35), mb_convert_case(_t(29), MB_CASE_TITLE), _t(48), _t(34)];
    }
    
    private function getRoleTableInfo() {
        $this->table_content = db_select(ROLES)
                ->orderBy("ID")
                ->limit(PAGE_SIZE_LIMIT, ($this->page-1)*PAGE_SIZE_LIMIT)
                ->execute()->fetchAll(PDO::FETCH_NUM);
        $this->table_headers = ["ID", _t(49)];
        require 'add_role_modal.php';
    }
    
    function getTranslationInfo() {
        $this->table_content = db_select(TRANSLATIONS)
                ->orderBy("ID")
                ->limit(PAGE_SIZE_LIMIT, ($this->page-1)*PAGE_SIZE_LIMIT)
                ->execute()->fetchAll(PDO::FETCH_NUM);
        $this->table_headers = ["ID", "EN", "TR"];
    }
}