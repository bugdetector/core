<?php
class AdminTableController extends AdminController{
    const TEXT_SIZE_LIMIT = 64;
    private $table;
    private $table_header = [];
    private $table_content = [];
    private $filter_options = [];
    private $total_count;
    private $page;
    private $offset;
    private $query_link;
    protected function preprocessPage()
    {
        parent::preprocessPage();
        $select = NULL;
        $this->table = NULL;
        $this->page = isset($_GET["page"]) && intval($_GET["page"]) ? intval($_GET["page"]) : 1;
        $this->offset = ($this->page-1)*PAGE_SIZE_LIMIT;

        if(isset($this->arguments[0]) && !in_array($this->arguments[0], CoreDB::get_information_scheme())){
            Utils::core_go_to(BASE_URL."/admin/table");
        }
        $query = NULL;
        if(count($this->arguments) == 4 && $this->arguments[1] == "fk"){
            $description = CoreDB::get_foreign_key_description($this->arguments[0], $this->arguments[2]);
            $this->table = $description[0];
            $query = db_select($this->table)
                    ->condition("ID = :id", [":id"=> intval($this->arguments[3]) ]);
                           
        } elseif (isset($this->arguments[0])) {
            $this->table = $this->arguments[0];
            $query = db_select($this->table);
        } else {
            $this->create_warning_message(_t(51), "alert-info");
        }
        if($this->table && $query){
            $query->select_with_function([
                'CONCAT(\'<a href="#" title="'._t(82).'"><span class="glyphicon glyphicon-remove rowdelete core-control"></span></a>'.
                '<a href="'.BASE_URL.'/admin/insert/'.$this->table.'/\', ID,\'" title="'._t(83).'"><span class="glyphicon glyphicon-eye-open rowbrowse core-control"></span> </a>\') as controls'
            ]);
            $params = $_GET;
            $this->table_header[] = "";
            $description = CoreDB::get_table_description($this->table, true);
            foreach($description as $desc){
                $this->table_header[$desc["Field"]] = $desc["Field"];
                $this->filter_options[] = [
                    "name" => $desc["Field"],
                    "type" => "text",
                    "label" => $desc["Field"],
                    "value" => isset($params[$desc["Field"]]) ? $params[$desc["Field"]] : ""
                ];
                if($desc["Type"] == "tinytext"){ //File input
                    $query->select_with_function([
                        "CONCAT('<p class=\'".$this->getClassByDataType($desc["Type"], $desc["Key"])."\'>
                        <a href=\'".BASE_URL."/files/uploaded/{$this->table}/{$desc["Field"]}/', `".$desc["Field"]."`, '\' target=\'_blank\'>' , `".$desc["Field"]."`, '</a></p>')"
                    ]);
                }else{
                    $query->select_with_function([
                        "CONCAT('<p class=\'".$this->getClassByDataType($desc["Type"], $desc["Key"])."\'>', `".$desc["Field"]."`, '</p>')"
                    ]);
                }
            }

            $order_by = isset($params["orderby"]) && in_array($params["orderby"], $this->table_header) ? $params["orderby"] : "ID";
            $order_direction = isset($params["orderdirection"]) && $params["orderdirection"] == "DESC" ? "DESC" : "ASC";

            $this->query_link = "?".http_build_query($params);
            unset($params["page"], $params["orderby"], $params["orderdirection"]);

            foreach($params as $key => $value){
                if(in_array($key, $this->table_header )){
                    $query->condition(" `$key` LIKE :$key ", [":$key" => "%".$value."%"]);
                }
            }
            
            $this->table_content = $query->limit(PAGE_SIZE_LIMIT, $this->offset)->orderBy("`$order_by` $order_direction")->execute()->fetchAll(PDO::FETCH_ASSOC);
            $query->unset_fields();
            $this->total_count = $query->limit(0)->select_with_function(["count(*) as count"])->execute()->fetchObject()->count;
        }
        $this->add_js("var control_table = '{$this->table}';");
        $this->add_js("var columns = ".json_encode(array_values($this->table_header)).";");
        $this->add_js("$('.list-group-item.tablelist a:textEquals(\"{$this->table}\")').parent().addClass('active');");
        $this->add_frontend_translation(93);
        $this->add_frontend_translation(109);
    }

    protected function echoContent() {
        $this->import_view("table_view");
        require 'table_html.php';
    }
    
    private function getClassByDataType($type = "", $key = ""){
        if ($key == "MUL") {
            return "dbl_click_fk";
        } else if($type == "tinytext"){
            return 'dbl_click_file';
        }
        return;
    }
    

}