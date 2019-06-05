<?php
class TableController extends AdminPage{
    
    
    protected function echoContent() {
        $this->add_frontend_translation(93);
        $this->add_frontend_translation(109);
        
        if(isset($this->arguments[1]) && $this->arguments[1] == "new"){
            require 'new/new.php';
            $page = new NewTableController($this->arguments);
            $page->echoContent();
            return;
        }        
        $select = NULL;
        $table = NULL;
        $page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
        $page == 0 ? ($page = 1) : NOEXPR;
        $offset = ($page-1)*PAGE_SIZE_LIMIT;
        $query_link = "";
        $table_exist = TRUE;
        if(isset($this->arguments[1])){
           $table_exist = in_array($this->arguments[1], get_information_scheme());
        }
        if(!$table_exist){
            core_go_to(BASE_URL."/admin/table");
        }
        if(isset($this->arguments[1]) && isset($this->arguments[2]) && isset($this->arguments[3])){
            $description = get_foreign_key_description($this->arguments[1], $this->arguments[2])->fetch(PDO::FETCH_NUM);
            $table = $description[0];
            $query = db_select($table)
                    ->condition("ID = :id", ["id"=> intval($this->arguments[3]) ]);
            $columns = $query->limit(PAGE_SIZE_LIMIT, $offset)->execute()->fetchAll(PDO::FETCH_NUM);
            $count = $query->select("", ["count(*)"])->execute()->fetch(PDO::FETCH_NUM);
            $select = ["values" => $columns];
            $select["count"] = $count[0];
            $select["skeleton"] = get_table_description($table);
            $query_link = "/$table";                
        } elseif (isset($this->arguments[1])) {
            $table = $this->arguments[1];
            $params = $_GET;
            unset($params["page"]);
            $condition_query = "";
            $index = 0;
            foreach ($params as $key => $param){
                $params[$key] = "%$param%";
                $condition_query.= ($condition_query ? "AND " : "")."$key LIKE :$key ";
                $index++;
            }
            $results = db_select($table)
                    ->condition($condition_query)
                    ->params($params)
                    ->limit(PAGE_SIZE_LIMIT, $offset)->execute()->fetchAll(PDO::FETCH_NUM);
            $count = db_select($table)
                    ->condition($condition_query)
                    ->params($params)
                    ->select("", ["count(*)"])->execute()->fetch(PDO::FETCH_NUM);
            $select = ["values" => $results];
            $select["count"] = $count[0];
            $select["skeleton"] = get_table_description($table);
            
            $query_link = "?".http_build_query($params);
        } else {
            create_warning_message(_t(51), "alert-info");
        }
        require 'table_html.php';
        echo_tablolar($this, $select, $table, $page, $offset, $query_link);
    }
    
    

}