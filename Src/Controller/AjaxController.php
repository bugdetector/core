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
            $query = \CoreDB::database()->select($table)
                ->select($table, ["ID", $column])
                ->condition($column, $data, "LIKE")
                ->condition($column, "''", "!=")
                ->condition($column, "NULL", "IS NOT")
                ->limit(20);
            if (isset($_POST["filter-column"]) && isset($_POST["filter-value"])) {
                $filter_column = preg_replace('/[^a-zA-Z1-9_]*/', '', $_POST["filter-column"]);
                $query->condition($filter_column, $_POST["filter-value"])
                ->condition($filter_column, "''", "!=")
                ->condition($filter_column, "NULL", "IS NOT");
            }
            $filtered_result = $query->execute()->fetchAll(\PDO::FETCH_NUM);
            echo json_encode($filtered_result);
        }
    }
}
