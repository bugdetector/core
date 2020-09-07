<?php

namespace CoreDB\Kernel;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use Exception;
use PDO;
use ReflectionObject;
use Src\Entity\File;
use Src\Entity\Translation;
use Src\Form\TableInsertForm;

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
    public function __get($name)
    {
        return isset($this->{$name}) ? $this->{$name} : null;
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
        $reflector = new ReflectionObject($this);
        $nodes = $reflector->getProperties();
        $object_as_array = [];
        foreach ($nodes as $node) {
            $nod = $reflector->getProperty($node->getName());
            $nod->setAccessible(true);
            $object_as_array[$node->getName()] = $nod->getValue($this);
        }
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
        return CoreDB::database()->truncate($table)->execute();
    }


    public function getFileUrlForField($field_name)
    {
        return BASE_URL."/files/uploaded/$this->table/$field_name/".$this->$field_name;
    }

    public function getForm()
    {
        return new TableInsertForm($this);
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

    public static function editUrl(string $table_name, $data){
        return BASE_URL."/admin/table/insert/{$table_name}/{$data}";
    }

    function include_files($from = null)
    {
        foreach (\CoreDB::normalizeFiles($from) as $file_key => $fileInfo) {
            if ($fileInfo["size"] != 0) {
                if($this->$file_key){
                    $file = File::get(["ID" => $this->$file_key]);
                    $file->unlinkFile();
                }else{
                    $file = new File();
                }
                if ($file->storeUploadedFile($this->table, $file_key, $fileInfo)) {
                    $this->{$file_key} = $file->ID;
                    $this->save();
                }else{
                    \CoreDB::database()->rollback();
                    throw new Exception(Translation::getTranslation(99));
                }
            }
        }
    }
}
