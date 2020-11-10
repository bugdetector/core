<?php

namespace Src\Controller;

use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\ServiceController;
use Src\Entity\Translation;
use Src\Form\Widget\CollapsableWidgetGroup;
use Src\Views\CollapsableCard;
use Src\Views\ViewGroup;

class AjaxController extends ServiceController
{

    public function autocompleteFilter()
    {
        $autocompleteToken = @$_POST["token"];
        $data = @$_POST["data"];
        if ($autocompleteToken && isset($_SESSION["autocomplete"][$autocompleteToken])) {
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

    public function getEntityCard()
    {
        $entityName = @$_POST["entity"];
        $index = @$_POST["index"] + 1;
        $name = @$_POST["name"];
        $hiddenFields = @$_POST["hiddenFields"];
        $referenceClass = \CoreDB::config()->getEntityInfo($entityName)["class"];
        $object = new $referenceClass();

        $this->response_type = self::RESPONSE_TYPE_RAW;
        echo CollapsableWidgetGroup::getObjectCard(
            $object,
            $name,
            $index,
            $hiddenFields
        );
    }
}
