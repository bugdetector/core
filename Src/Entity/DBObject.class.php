<?php
namespace Src\Entity;

use CoreDB\Kernel\Database\DeleteQueryPreparer;
use CoreDB\Kernel\Database\InsertQueryPreparer;
use CoreDB\Kernel\Database\SelectQueryPreparer;
use CoreDB\Kernel\Database\UpdateQueryPreparer;
use Exception;
use PDO;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;

class DBObject
{
    public $table;
    protected $changed_fields = [];

    public $ID;
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
    * Set fields of object using an array with same keys
    * @param DBObject $object
    * @return \array
    */
    public function map(array $array)
    {
        $object_class_name = get_class($this);
        $this->changed_fields = [];
        foreach ($array as $key => $value) {
            if ($object_class_name != "Src\Entity\DBObject" && !property_exists($this, $key)) {
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

    public function __get($name)
    {
        return isset($this->{$name}) ? $this->{$name} : null;   
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

    /**
     * @param array $filter filter on key value pairs
     * @param string $table table name
     * @return DBObject
     */
    public static function get(array $filter, string $table)
    {
        $condition_sentence = "";
        $params = [];
        foreach ($filter as $key => $value) {
            $condition_sentence.= (!$condition_sentence ? "" : "AND")." `$key` = :$key ";
            $params[":$key"] = $value;
        }
        return (new SelectQueryPreparer($table))->condition($condition_sentence)->params($params)->orderBy("ID")->execute()
        ->fetchObject(get_called_class(), [$table]) ? : null;
    }

    public static function getAll(array $filter, string $table)
    {
        $condition_sentence = "";
        $params = [];
        foreach ($filter as $key => $value) {
            $condition_sentence.= (!$condition_sentence ? "" : "AND")." `$key` = :$key";
            $params[":$key"] = $value;
        }
        return (new SelectQueryPreparer($table))->condition($condition_sentence)->params($params)->orderBy("ID")->execute()
        ->fetchAll(PDO::FETCH_CLASS, get_called_class(), [$table]);
    }

    protected function insert()
    {
        $statement = ( new InsertQueryPreparer($this->table, $this->toArray()) )->execute();
        $this->ID = \CoreDB::database()::getInstance()->lastInsertId();
        return $statement;
    }
    
    protected function update()
    {
        return ( new UpdateQueryPreparer($this->table, $this->toArray()) )->condition("ID = :id", ["id" => $this->ID])->execute();
    }
    public function save()
    {
        if ($this->ID) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    
    public function delete()
    {
        if (!$this->ID) {
            return false;
        }
        $table_description = \CoreDB::database()::getTableDescription($this->table);
        foreach ($table_description as $field) {
            if ($field["Type"] == "tinytext") {
                $field_name = $field["Field"];
                \CoreDB::removeUploadedFile($this->table, $field_name, $this->$field_name);
            }
        }
        return (new DeleteQueryPreparer($this->table))->condition(" ID = :id ", ["id" => $this->ID])->execute();
    }

    public function get_file_url_for_field($field_name)
    {
        return BASE_URL."/files/uploaded/$this->table/$field_name/".$this->$field_name;
    }
    
    public function getFormFields($name) : array
    {
        $fields = [];
        $descriptions = \CoreDB::database()::getTableDescription($this->table);
        foreach ($descriptions as $description) {
            /**
             * @var FormWidget $field
             */
            $field = $this->getFieldInput($description);
            $field->setName($name."[{$description["Field"]}]")
            ->setLabel(Translation::getTranslation($description["Field"]))
            ->setValue(isset($this->{$description["Field"]}) ? strval($this->{$description["Field"]}) : "");
            $fields[] = $field;
        }
        return $fields;
    }
    protected function getFieldInput($description)
    {
        $input = \CoreDB::database()::get_supported_data_types()[$this->get_input_type($description["Type"], $description["Key"])]["input_field_callback"]($this, $description, $this->table);
        if (in_array($description["Field"], ["ID" ,"created_at", "last_updated"])) {
            $input->addAttribute("disabled", true);
        }
        return $input;
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
    
    function include_files($from = null)
    {
        foreach (\CoreDB::normalizeFiles($from) as $file_key => $file) {
            if ($file["size"] != 0) {
                $file["name"] = $this->ID."_".filter_var($file["name"], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
                \CoreDB::removeUploadedFile($this->table, $file_key, $this->$file_key);
                $this->$file_key = $file["name"];
                if (!\CoreDB::storeUploadedFile($this->table, $file_key, $file)) {
                    \CoreDB::database()::getInstance()->rollback();
                    throw new Exception(Translation::getTranslation(99));
                }
            }
        }
        $this->update();
    }
}
