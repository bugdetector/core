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
            $object = DBObject::get(["ID" => $id], $table);
            if ($object) {
                $object->delete();
                $this->createMessage(Translation::getTranslation("record_removed"), Messenger::SUCCESS);
            } else {
                throw new Exception(Translation::getTranslation("invalid_operation"));
            }
        }
    }

    /**
     * Returns foreign key entry
     */
    public function getForeignKeyEntry()
    {
        $description = \CoreDB::database()::getForeignKeyDescription($_POST["table"], $_POST["column"]);
        $object = DBObject::get(["ID" => intval($_POST["fk"])], $description[0]);
        $return_string = "";
        foreach ($object->toArray() as $key => $field) {
            $return_string .= "$key = $field ";
        }
        echo $return_string;
    }

    /**
     * Returns column definition for table definition
     */
    public function getColumnDefinition()
    {
        echo ColumnDefinition::create("fields[{$_POST["index"]}]")->setSortable(true);
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
            DBObject::clear($tablename);
            $this->createMessage(Translation::getTranslation("table_truncated", [$tablename]), Messenger::SUCCESS);
        }
    }

    public function langimp()
    {
        try {
            Translation::importTranslations();
            $this->createMessage(Translation::getTranslation("import_success"), Messenger::SUCCESS);
        } catch (Exception $ex) {
            $this->createMessage($ex->getMessage());
        }
    }

    public function langexp()
    {
        try {
            Translation::exportTranslations();
            $this->createMessage(Translation::getTranslation("export_success"), Messenger::SUCCESS);
        } catch (Exception $ex) {
            $this->createMessage($ex->getMessage());
        }
    }

    public function tableConfigurationExport()
    {
        CoreDB::config()->exportTableConfiguration();
        $this->createMessage(Translation::getTranslation("export_success"), Messenger::SUCCESS);
    }

    public function tableConfigurationImport()
    {
        CoreDB::config()->importTableConfiguration();
        $this->createMessage(Translation::getTranslation("import_success"), Messenger::SUCCESS);
    }

    public function clearCache()
    {
        CoreDB::config()->clearCache();
        $this->createMessage(Translation::getTranslation("cache_cleared"), Messenger::SUCCESS);
    }
}
