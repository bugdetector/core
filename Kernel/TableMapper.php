<?php

namespace CoreDB\Kernel;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\TableDefinition;
use Exception;
use PDO;
use PDOException;
use ReflectionObject;
use Src\Entity\File;
use Src\Entity\Translation;
use Src\Form\TableInsertForm;

abstract class TableMapper
{    
    public Integer $ID;
    public DateTime $created_at;
    public DateTime $last_updated;
    
    protected $changed_fields;

    public function __construct()
    {
        $table_definition = TableDefinition::getDefinition($this->getTableName());
        /**
         * @var DataTypeAbstract $field
         */
        foreach($table_definition->fields as $field_name => $field){
            $this->{$field_name} = $field;
        }
    }
    public function __get($name)
    {
        return isset($this->{$name}) ? $this->{$name} : null;
    }

    /**
     * Return associated table name.
     * @return string
     *  Table name.
     */
    abstract public static function getTableName() : string;
    
    /**
     * Get an instance of object with given filter
     * @return TableMapper
     *  Object.
     */
    public static function get(array $filter){
        return static::find($filter, static::getTableName());
    }

    /**
     * Get all objects matches given filter.
     * @return array
     *  TableMapper objects.
     */
    public static function getAll(array $filter) : array{
        return static::findAll($filter, static::getTableName());
    }


    /**
     * Copy of ::get. Needs table name.
     * @return TableMapper
     *  Object.
     */
    public static function find(array $filter, string $table) : ?TableMapper
    {
        $query = \CoreDB::database()->select($table);
        foreach ($filter as $key => $value) {
            $query->condition($key, $value);
        }
        $result = $query->orderBy("ID")
        ->execute()
        ->fetch(\PDO::FETCH_ASSOC) ? : null;
        if($result){
            $className = get_called_class();
            /**
             * @var TableMapper
             */
            $object = new $className($table);
            $object->map($result);
        }else{
            $object = null;
        }
        return $object;
    }

    /**
     * Copy of ::getAll. Need table name.
     * @return array
     *  TableMapper objects.
     */
    public static function findAll(array $filter, string $table) : array
    {
        $query = CoreDB::database()->select($table);
        foreach ($filter as $key => $value) {
            $query->condition($key, $value);
        }
        $results = $query->orderBy("ID")
        ->execute()
        ->fetchAll(PDO::FETCH_ASSOC);
        $objects = [];
        if($results){
            $className = get_called_class();
            foreach($results as $result){
                /**
                 * @var TableMapper
                 */
                $object = new $className($table);
                $object->map($result);
                $objects[] = $object;
            }
        }
        return $objects;
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
            $this->$key->setValue($value);
        }
    }

    /**
     * Converts an object to array including private fields
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
            $field = $nod->getValue($this);
            if( !($field instanceof DataTypeAbstract) || in_array($node->getName(), ["ID", "table", "created_at", "last_updated", "changed_fields"]) ){
                continue;
            }
            $object_as_array[$node->getName()] = $field->getValue();
        }
        return $object_as_array;
    }


    protected function insert()
    {
        $statement = CoreDB::database()->insert($this->getTableName(), $this->toArray())->execute();
        $this->ID->setValue(\CoreDB::database()->lastInsertId());
        return $statement;
    }

    protected function update()
    {
        return CoreDB::database()
        ->update($this->getTableName(), $this->toArray())
        ->condition("ID", $this->ID->getValue())
        ->execute();
    }

    public function save()
    {
        if ($this->ID->getValue()) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    public function delete() : bool
    {
        if (!$this->ID->getValue()) {
            return false;
        }
        /**
         * @var DataTypeAbstract $field
         */
        foreach($this->toArray() as $field_name => $field){
            if ($field instanceof \CoreDB\Kernel\Database\DataType\File) {
                if($file = File::get(["ID" => $field->getValue()])){
                    $file->unlinkFile();
                }
            }
        }
        return boolval(
            CoreDB::database()->delete($this->getTableName())->condition("ID", $this->ID)->execute()
        );
    }

    /**
     * Truncate associated table.
     * @throws PDOException
     */
    public static function clear(){
        return static::clear(static::getTableName());
    }
    protected static function clearTable($table)
    {
        return CoreDB::database()->truncate($table)->execute();
    }


    public function getFileUrlForField($field_name)
    {
        return BASE_URL."/files/uploaded/".$this->getTableName()."/$field_name/".$this->$field_name;
    }

    public function getForm()
    {
        return new TableInsertForm($this);
    }
    
    public function getFormFields($name) : array
    {
        $fields = [];
        $reflector = new ReflectionObject($this);
        $nodes = $reflector->getProperties();
        foreach ($nodes as $node) {
            $nod = $reflector->getProperty($node->getName());
            $nod->setAccessible(true);
            $field = $nod->getValue($this);
            if( !($field instanceof DataTypeAbstract) || in_array($node->getName(), ["ID", "table", "created_at", "last_updated", "changed_fields"]) ){
                continue;
            }
            $column_name = $node->getName();
            $fields[$column_name] = $field->getWidget()->setName($name."[{$column_name}]")
            ->setLabel(Translation::getTranslation($column_name));
        }
        return $fields;
    }

    public static function editUrl(string $table_name, $data){
        return BASE_URL."/admin/table/insert/{$table_name}/{$data}";
    }

    function includeFiles($from = null)
    {
        foreach (\CoreDB::normalizeFiles($from) as $file_key => $fileInfo) {
            if ($fileInfo["size"] != 0) {
                if($this->$file_key->getValue()){
                    $file = File::get(["ID" => $this->$file_key]);
                    $file->unlinkFile();
                }else{
                    $file = new File();
                }
                if ($file->storeUploadedFile($this->getTableName(), $file_key, $fileInfo)) {
                    $this->{$file_key}->setValue($file->ID);
                    $this->save();
                }else{
                    \CoreDB::database()->rollback();
                    throw new Exception(Translation::getTranslation(99));
                }
            }
        }
    }
}
