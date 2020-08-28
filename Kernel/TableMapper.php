<?php

namespace CoreDB\Kernel;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use Exception;
use PDO;
use Src\Entity\Translation;

abstract class TableMapper
{
    public $table;
    
    public $ID;
    public $created_at;
    public $last_updated;
    
    protected $changed_fields;

    public function __construct(string $table)
    {
        $this->table = $table;
    }
    
    abstract public static function get(array $filter);
    abstract public static function getAll(array $filter) : array;

    public static function find(array $filter, string $table) : ?TableMapper
    {
        $query = CoreDB::database()->select($table);
        $params = [];
        foreach ($filter as $key => $value) {
            $query->condition("`$key` = :$key");
            $params[":$key"] = $value;
        }
        return $query->params($params)
        ->orderBy("ID")
        ->execute()
        ->fetchObject(get_called_class(), [$table]) ? : null;
    }

    public static function findAll(array $filter, string $table) : array
    {
        $query = CoreDB::database()->select($table);
        $params = [];
        foreach ($filter as $key => $value) {
            $query->condition("`$key` = :$key");
            $params[":$key"] = $value;
        }
        return $query->params($params)
        ->orderBy("ID")
        ->execute()
        ->fetchAll(PDO::FETCH_CLASS, get_called_class(), [$table]) ? : [];
    }

    /**
    * Set fields of object using an array with same keys
    * @param array $array
    *  Containing field values to set
    */
    public function map(array $array)
    {
        $this->changed_fields = [];
        foreach ($array as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }
            if ($this->{$key} != $value) {
                $this->changed_fields[$key] = [
                    "old_value" => $this->{$key},
                    "new_value" => $value
                ];
            }
            $this->$key = $value;
        }
    }

    /**
     * Converts an object to array including private fields
     * @param DBObject $object
     * @return \array
     */
    public function toArray() : array
    {
        $object_as_array = get_object_vars($this);
        unset($object_as_array["ID"]);
        unset($object_as_array["table"]);
        unset($object_as_array["created_at"]);
        unset($object_as_array["last_updated"]);
        unset($object_as_array["changed_fields"]);
        return $object_as_array;
    }


    protected function insert()
    {
        $statement = CoreDB::database()->insert($this->table, $this->toArray())->execute();
        $this->ID = \CoreDB::database()->lastInsertId();
        return $statement;
    }

    protected function update()
    {
        return CoreDB::database()->update($this->table, $this->toArray())->condition("ID = :id", ["id" => $this->ID])->execute();
    }

    public function save()
    {
        if ($this->ID) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    public function delete() : bool
    {
        if (!$this->ID) {
            return false;
        }
        $table_description = \CoreDB::database()::getTableDescription($this->table);
        /**
         * @var DataTypeAbstract $field
         */
        foreach ($table_description as $field) {
            if ($field instanceof \CoreDB\Kernel\Database\DataType\File) {
                \CoreDB::removeUploadedFile($this->table, $field->column_name, $this->{$field->column_name});
            }
        }
        return boolval(
            CoreDB::database()->delete($this->table)->condition(" ID = :id ", ["id" => $this->ID])->execute()
        );
    }

    abstract public static function clear();
    protected static function clearTable($table)
    {
        CoreDB::database()->truncate($table)->execute();
    }


    public function getFileUrlForField($field_name)
    {
        return BASE_URL."/files/uploaded/$this->table/$field_name/".$this->$field_name;
    }

    protected function get_input_type(string $dataType, $key = "")
    {
        if (strpos($key, "MUL") !== false) {
            return "MUL";
        } elseif (strpos($dataType, "int") === 0) {
            return "INT";
        } elseif (strpos($dataType, "varchar") === 0) {
            return "VARCHAR";
        } elseif (strpos($dataType, "datetime")===0) {
            return "DATETIME";
        } else {
            return strtoupper($dataType);
        }
    }
    
    public function getFormFields($name) : array
    {
        $fields = [];
        $descriptions = \CoreDB::database()::getTableDescription($this->table);
        /**
         * @var DataTypeAbstract $description
         */
        foreach ($descriptions as $column_name => $description) {
            if (in_array($column_name, ["ID", "created_at", "last_updated"])) {
                continue;
            }
            /**
             * @var FormWidget $field
             */
            $field = $description->getWidget();
            $field->setName($name."[{$description->column_name}]")
            ->setLabel(Translation::getTranslation($description->column_name))
            ->setValue(isset($this->{$description->column_name}) ? strval($this->{$description->column_name}) : "");
            $fields[$description->column_name] = $field;
        }
        return $fields;
    }

    function include_files($from = null)
    {
        foreach (\CoreDB::normalizeFiles($from) as $file_key => $file) {
            if ($file["size"] != 0) {
                $file["name"] = $this->ID."_".filter_var($file["name"], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
                \CoreDB::removeUploadedFile($this->table, $file_key, $this->$file_key);
                $this->$file_key = $file["name"];
                if (!\CoreDB::storeUploadedFile($this->table, $file_key, $file)) {
                    \CoreDB::database()->rollback();
                    throw new Exception(Translation::getTranslation(99));
                }
            }
        }
        $this->update();
    }
}
