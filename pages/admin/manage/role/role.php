<?php

class AdminManageRoleController extends AdminManageController{
    
    protected function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(_t("role_management"));

        $this->table_headers = ["ID" => "ID", "role" => _t("role_name")];

        $params = array_filter($_GET);
        $order_by = isset($params["orderby"]) && in_array($params["orderby"], array_keys($this->table_headers)) ? $params["orderby"] : "ID";
        $order_direction = isset($params["orderdirection"]) && $params["orderdirection"] == "DESC" ? "DESC" : "ASC";
        unset($params["orderby"], $params["orderdirection"]);
        
        $query = db_select("roles")
        ->orderBy($order_by." ".$order_direction);
        foreach($params as $key => $value){
            if(in_array($key, array_keys($this->table_headers))){
                $query->condition(" $key LIKE :$key ", [":$key" => "%".$value."%"]);
            }
        }

        $this->entry_count = $query->select_with_function(["COUNT(*) AS count"])->execute()->fetchObject()->count;
        $query->unset_fields();
        $this->table_content = $query
        ->select("roles", ["ID", "role"])
        ->select_with_function([
            "CONCAT(\"<a href='#' class='remove-role' data-role-name='\", role, \"'>"._t("remove_role")."</a>\") AS remove_link"
        ])
        ->limit(PAGE_SIZE_LIMIT, ($this->page-1)*PAGE_SIZE_LIMIT)->execute()->fetchAll(PDO::FETCH_ASSOC);


        $this->action_section = Group::create("d-sm-inline-block btn btn-sm btn-primary shadow-sm add-role")
        ->setTagName("a")->addAttribute("href", "#")->addField(
            TextElement::create("<i class='fas fa-plus text-white-50'></i> " . _t("add_role"))
        );

        $this->add_js_files("pages/admin/manage/role/role.js");
    }

    protected function add_default_translations()
    {
        parent::add_default_translations();
        $this->add_frontend_translation("add_role");
        $this->add_frontend_translation("role_name");
        $this->add_frontend_translation("add");
        $this->add_frontend_translation("record_remove_accept");
    }
}