<?php
namespace Src\Controller;

use CoreDB\Kernel\ServiceController;

class AjaxController extends ServiceController
{

    public function autocompleteFilter()
    {
        $autocompleteToken = @$_POST["token"];
        $data = @$_POST["data"];
        if($autocompleteToken && isset($_SESSION["autocomplete"][$autocompleteToken])){
            $autocompleteData = $_SESSION["autocomplete"][$autocompleteToken];
            $referenceTable = $autocompleteData["referenceTable"];
            $referenceColumn = $autocompleteData["referenceColumn"];
            
            $data = "%{$data}%";
            $query = \CoreDB::database()->select($referenceTable)
                ->select($referenceTable, ["ID", $referenceColumn])
                ->condition($referenceColumn, $data, "LIKE")
                ->limit(20);
            return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
        }
    }
}
