<?php

class AdminManageController extends AdminController{
    public $operation;
    public $table_headers;
    public $table_content;
    public $entry_count;
    public $page;
    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->operation = isset($this->arguments[0]) ? $this->arguments[0] : "";
    }
    
    protected function preprocessPage() {
        parent::preprocessPage();
        if(isset($_POST["ROLE"])){
            $role = new DBObject(ROLES);
            $role->ROLE = $_POST["ROLE"];
            $role->insert(); 
            $this->create_warning_message(_t(78, [$role->ROLE]),"alert-success");
        }
        $this->add_frontend_translation(98);
        $this->add_frontend_translation(103);
        $this->add_frontend_translation(104);
        
        $this->page = isset($_GET["page"]) && $_GET["page"]>1 ? $_GET["page"] : 1;
        
        switch ($this->operation){
        case "user":
            $this->getUserTableInfo();
            $this->setTitle(_t(2).": "._t(5));
            break;
        case "role":
            $this->getRoleTableInfo();
            $this->setTitle(_t(2).": "._t(6));
            break;
        case "translation":
            $this->getTranslationInfo();
            $this->setTitle(_t(2).": "._t(100));
            break;
        default:
            $this->setTitle(_t(2));
        }
    }

    protected function echoContent() {        
        require 'manage_html.php';
    }
    
    private function getUserTableInfo() {
        $query = db_select(USERS)
        ->orderBy("ID")->condition("USERNAME != 'guest'");
        $this->entry_count = $query->select_with_function(["COUNT(*) AS count"])->execute()->fetchObject()->count;
        $query->unset_fields();
        $this->table_content = $query->select(USERS, ["ID", "USERNAME", "NAME", "SURNAME", "EMAIL","PHONE", "CREATED_AT", "ACCESS"])->limit(PAGE_SIZE_LIMIT, ($this->page-1)*PAGE_SIZE_LIMIT)->execute()->fetchAll(PDO::FETCH_NUM);
        $this->table_headers = ["ID", _t(20), _t(27),mb_convert_case(_t(28),MB_CASE_TITLE), _t(35), mb_convert_case(_t(29), MB_CASE_TITLE), _t(48), _t(34)];
    }
    
    private function getRoleTableInfo() {
        $query = db_select(ROLES)
        ->orderBy("ID");
        $this->entry_count = $query->select_with_function(["COUNT(*) AS count"])->execute()->fetchObject()->count;
        $query->unset_fields();
        $this->table_content = $query->limit(PAGE_SIZE_LIMIT, ($this->page-1)*PAGE_SIZE_LIMIT)->execute()->fetchAll(PDO::FETCH_NUM);
        $this->table_headers = ["ID", _t(49)];
        require 'add_role_modal.php';
    }
    
    function getTranslationInfo() {
        $this->entry_count = 0;
        $this->table_content = db_select(TRANSLATIONS)->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
        $this->table_headers = ["ID", "EN", "TR"];
    }
}