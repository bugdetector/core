<?php

class AdminManageController extends AdminController{
    public $operation;
    public $table_headers;
    public $table_content;
    private $filter_options;
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
        $this->add_frontend_translation(50);
        $this->add_frontend_translation(11);
        $this->add_frontend_translation(14);
        
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
        $this->table_headers = [
            "ID" => "ID", 
            "USERNAME" => _t(20), 
            "NAME" => _t(27),
            "SURNAME" => mb_convert_case(_t(28),MB_CASE_TITLE), 
            "EMAIL" => _t(35), 
            "PHONE" => mb_convert_case(_t(29), MB_CASE_TITLE), 
            "CREATED_AT" => _t(48), 
            "ACCESS" => _t(34)
        ];
        $params = array_filter($_GET);
        $order_by = isset($params["orderby"]) && in_array($params["orderby"], array_keys($this->table_headers)) ? $params["orderby"] : "ID";
        $order_direction = isset($params["orderdirection"]) && $params["orderdirection"] == "DESC" ? "DESC" : "ASC";
        unset($params["orderby"], $params["orderdirection"]);


        $query = db_select(USERS)
        ->orderBy($order_by." ".$order_direction)
        ->condition("USERNAME != 'guest'");
        foreach($params as $key => $value){
            if(in_array($key, array_keys($this->table_headers))){
                $query->condition(" $key LIKE :$key ", [":$key" => "%".$value."%"]);
            }
        }

        $this->entry_count = $query->select_with_function(["COUNT(*) AS count"])->execute()->fetchObject()->count;
        $query->unset_fields();
        $this->table_content = $query
        ->select(USERS, ["ID", "USERNAME", "NAME", "SURNAME", "EMAIL","PHONE", "CREATED_AT", "ACCESS"])
        ->select_with_function([
            "CONCAT(\"<a href='".BASE_URL."/admin/user/\", USERNAME, \"'>"._t(9)."</a>\") AS edit_link",
            "CONCAT(\"<a href='#' class='delete-user' data-username='\", USERNAME, \"'>"._t(10)."</a>\") AS remove_link"
            ])
        ->limit(PAGE_SIZE_LIMIT, ($this->page-1)*PAGE_SIZE_LIMIT)->execute()->fetchAll(PDO::FETCH_ASSOC);

        $this->filter_options = [
            [
                "name" => "ID",
                "type" => "number",
                "label" => "ID",
                "value" => isset($params["ID"]) ? $params["ID"] : ""
            ],
            [
                "name" => "USERNAME",
                "type" => "text",
                "label" => "USERNAME",
                "value" => isset($params["USERNAME"]) ? $params["USERNAME"] : ""
            ],
            [
                "name" => "NAME",
                "type" => "text",
                "label" => "NAME",
                "value" => isset($params["NAME"]) ? $params["NAME"] : ""
            ],
            [
                "name" => "SURNAME",
                "type" => "text",
                "label" => "SURNAME",
                "value" => isset($params["SURNAME"]) ? $params["SURNAME"] : ""
            ],
            [
                "name" => "EMAIL",
                "type" => "text",
                "label" => "EMAIL",
                "value" => isset($params["EMAIL"]) ? $params["EMAIL"] : ""
            ],
            [
                "name" => "PHONE",
                "type" => "text",
                "label" => "PHONE",
                "value" => isset($params["PHONE"]) ? $params["PHONE"] : ""
            ],
            [
                "name" => "CREATED_AT",
                "type" => "text",
                "label" => "CREATED_AT",
                "value" => isset($params["CREATED_AT"]) ? $params["CREATED_AT"] : "",
                "class" => "datetimeinput"
            ],
            [
                "name" => "ACCESS",
                "type" => "text",
                "label" => "ACCESS",
                "value" => isset($params["ACCESS"]) ? $params["ACCESS"] : "",
                "class" => "datetimeinput"
            ]
        ];
    }
    
    private function getRoleTableInfo() {
        $this->table_headers = ["ID" => "ID", "ROLE" => _t(49)];

        $params = array_filter($_GET);
        $order_by = isset($params["orderby"]) && in_array($params["orderby"], array_keys($this->table_headers)) ? $params["orderby"] : "ID";
        $order_direction = isset($params["orderdirection"]) && $params["orderdirection"] == "DESC" ? "DESC" : "ASC";
        unset($params["orderby"], $params["orderdirection"]);
        
        $query = db_select(ROLES)
        ->orderBy($order_by." ".$order_direction);
        foreach($params as $key => $value){
            if(in_array($key, array_keys($this->table_headers))){
                $query->condition(" $key LIKE :$key ", [":$key" => "%".$value."%"]);
            }
        }

        $this->entry_count = $query->select_with_function(["COUNT(*) AS count"])->execute()->fetchObject()->count;
        $query->unset_fields();
        $this->table_content = $query
        ->select(ROLES, ["*"])
        ->select_with_function([
            "CONCAT(\"<a href='#' class='remove-role' data-role-name='\", ROLE, \"'>"._t(12)."</a>\") AS remove_link"
        ])
        ->limit(PAGE_SIZE_LIMIT, ($this->page-1)*PAGE_SIZE_LIMIT)->execute()->fetchAll(PDO::FETCH_ASSOC);
        
        $this->filter_options = [
            [
                "name" => "ID",
                "type" => "number",
                "label" => "ID",
                "value" => isset($params["ID"]) ? $params["ID"] : ""
            ],
            [
                "name" => "ROLE",
                "type" => "text",
                "label" => "ROLE",
                "value" => isset($params["ROLE"]) ? $params["ROLE"] : ""
            ]
        ];
    }
    
    function getTranslationInfo() {
        $this->table_headers = ["ID" => "ID", "EN" => "EN", "TR" => "TR"];

        $params = array_filter($_GET);
        $order_by = isset($params["orderby"]) && in_array($params["orderby"], array_keys($this->table_headers)) ? $params["orderby"] : "ID";
        $order_direction = isset($params["orderdirection"]) && $params["orderdirection"] == "DESC" ? "DESC" : "ASC";
        unset($params["orderby"], $params["orderdirection"]);

        $query = db_select(TRANSLATIONS)->orderBy($order_by." ".$order_direction);
        foreach($params as $key => $value){
            if(in_array($key, array_keys($this->table_headers))){
                $query->condition(" $key LIKE :$key ", [":$key" => "%".$value."%"]);
            }
        }

        $this->entry_count = 0;
        $this->table_content = $query->execute()->fetchAll(PDO::FETCH_NUM);
        $this->table_headers = ["ID" => "ID", "EN" => "EN", "TR" => "TR"];

        $this->filter_options = [
            [
                "name" => "ID",
                "type" => "number",
                "label" => "ID",
                "value" => isset($params["ID"]) ? $params["ID"] : ""
            ],
            [
                "name" => "EN",
                "type" => "text",
                "label" => "EN",
                "value" => isset($params["EN"]) ? $params["EN"] : ""
            ],
            [
                "name" => "TR",
                "type" => "text",
                "label" => "TR",
                "value" => isset($params["TR"]) ? $params["TR"] : ""
            ]
        ];
    }
}