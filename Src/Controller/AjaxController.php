<?php
namespace Src\Controller;

use CoreDB\Kernel\ServiceController;

class AjaxController extends ServiceController
{

    private function AutoCompleteSelectBoxFilter()
    {
        $table = $_POST["table"];

        if (in_array($table, \CoreDB::database()::getTableList())) {
            $column = preg_replace('/[^a-zA-Z1-9_]*/', '', $_POST["column"]);
            ;
            $data = "%" . $_POST["data"] . "%";
            $query = db_select($table)
                ->select($table, ["ID", $column])
                ->condition(" $column LIKE :data AND $column != '' AND $column IS NOT NULL", [
                    ":data" => $data
                ])->limit(AUTOCOMPLETE_SELECT_BOX_LIMIT);
            if (isset($_POST["filter-column"]) && isset($_POST["filter-value"])) {
                $filter_column = preg_replace('/[^a-zA-Z1-9_]*/', '', $_POST["filter-column"]);
                $query->condition(
                    "$filter_column = :value AND $filter_column != '' AND $filter_column IS NOT NULL",
                    [":value" => $_POST["filter-value"]]
                );
            }
            $filtered_result = $query->execute()->fetchAll(PDO::FETCH_NUM);
            echo json_encode($filtered_result);
        }
    }
}
