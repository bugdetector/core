<?php

namespace Src\Controller\Admin;

use CoreDB;
use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\ServiceController;
use Exception;
use Src\Entity\Cache;
use Src\Entity\DBObject;
use Src\Entity\Translation;
use Src\Views\ColumnDefinition;
use Src\Views\TableAndColumnSelector;

class AjaxController extends ServiceController
{
    public function checkAccess(): bool
    {
        return \CoreDB::currentUser()->isAdmin();
    }

    /**
     * Delete record
     */
    public function delete()
    {
        if (in_array($_POST["table"], \CoreDB::database()::getTableList())) {
            $table = $_POST["table"];
            $id = $_POST["id"];
            $object = DBObject::get($id, $table);
            if ($object) {
                $object->delete();
                $this->createMessage(Translation::getTranslation("record_removed"), Messenger::SUCCESS);
            } else {
                throw new Exception(Translation::getTranslation("invalid_operation"));
            }
        }
    }

    /**
     * Returns column definition for table definition
     */
    public function getColumnDefinition()
    {
        $this->response_type = self::RESPONSE_TYPE_RAW;
        echo ColumnDefinition::create("fields[{$_POST["index"]}]")->setSortable(true);
    }


    public function getTableColumns(){
        $column_options = [];
        if($_POST["type"] == TableAndColumnSelector::TYPE_FIELD){
            $column_options["*"] = Translation::getTranslation("all");
        }
        $table = isset($_POST["table"]) ? $_POST["table"] : null;
        if($table){
            foreach(\CoreDB::database()->getTableDescription($table) as $fieldName => $field){
                $column_options[$fieldName] = $fieldName;
            }
        }
        return $column_options;
    }

    public function getTableAndColumnSelector(){
        $this->response_type = self::RESPONSE_TYPE_RAW;
        $index = $_POST["index"];
        $type = $_POST["type"];
        $name = $_POST["name"];
        $title = $type == TableAndColumnSelector::TYPE_COMPARISON ? Translation::getTranslation("filters") : Translation::getTranslation("result_fields");
        $widget = new TableAndColumnSelector($title, $name, $type);
        $widget->setValue(json_encode([
            $index => []
        ]));
        echo $widget->content->fields[0];
    }

    /**
     * Drops table
     */
    public function drop()
    {
        $tablename = $_POST["tablename"];
        if (in_array($tablename, \CoreDB::database()::getTableList())) {
            CoreDB::database()->drop($tablename)->execute();
            Cache::clear();
            $this->createMessage(Translation::getTranslation("table_deleted", [$tablename]), Messenger::SUCCESS);
        }
    }

    /**
     * Drops field
     */
    public function dropfield()
    {
        $tablename = $_POST["tablename"];
        $column = $_POST["column"];
        if (in_array($tablename, \CoreDB::database()::getTableList())) {
            CoreDB::database()->drop($tablename)->setColumn($column)->execute();
            Cache::clear();
            $this->createMessage(Translation::getTranslation("field_dropped", [$column]), Messenger::SUCCESS);
        }
    }

    /**
     * Truncates table
     */
    public function truncate()
    {
        $tablename = $_POST["tablename"];
        if (in_array($tablename, \CoreDB::database()::getTableList())) {
            $object = new DBObject($tablename);
            $object->clear($tablename);
            $this->createMessage(Translation::getTranslation("table_truncated", [$tablename]), Messenger::SUCCESS);
        }
    }

    /**
     * Clears cache table and twig cache
     */
    public function clearCache()
    {
        CoreDB::config()->clearCache();
        $this->createMessage(Translation::getTranslation("cache_cleared"), Messenger::SUCCESS);
    }
}
