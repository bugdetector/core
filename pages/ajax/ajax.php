<?php

class AjaxController extends ServicePage {
    
    public function callService(string $service_name) {
        $this->$service_name();
    }
    
    public function check_access() : bool {
        return User::get_current_core_user()->isAdmin();
    }
    
     private function AutoCompleteSelectBoxFilter(){
        $table = $_POST["table"];

        if(in_array($table, get_information_scheme()) ){
            $column = preg_replace('/[^a-zA-Z1-9_]*/', '', $_POST["column"]); ;
            $data = "%".$_POST["data"]."%";
            $query = db_select($table)
            ->select($table, ["ID", $column])
            ->condition(" $column LIKE :data AND $column != '' AND $column IS NOT NULL", [
                ":data" => $data
            ])->limit(AUTOCOMPLETE_SELECT_BOX_LIMIT);
            if(isset($_POST["filter-column"]) && isset($_POST["filter-value"]) ){
                $filter_column = preg_replace('/[^a-zA-Z1-9_]*/', '', $_POST["filter-column"]);
                $query->condition( "$filter_column = :value AND $filter_column != '' AND $filter_column IS NOT NULL", 
                [":value" => $_POST["filter-value"]]);
            }
            $filtered_result = $query->execute()->fetchAll(PDO::FETCH_NUM);
            echo json_encode($filtered_result);
        }
    }
}
