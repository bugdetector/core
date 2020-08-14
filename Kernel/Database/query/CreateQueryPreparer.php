<?php

namespace CoreDB\Kernel\Database;

use CoreDB\Kernel\Migration;

use Exception;
use PDOStatement;
use Src\Entity\Translation;

class CreateQueryPreparer extends QueryPreparer
{
    private $table_name;
    private $table_comment;
    private $fields = [];
    public function __construct(string $table_name)
    {
        $this->table_name = $table_name;
    }
    public function setFields(array $fields) : CreateQueryPreparer
    {
        $this->fields = $fields;
        return $this;
    }

    public function setComment(string $table_comment)
    {
        $this->table_comment = $table_comment;
        return $this;
    }
    public function addField(array $field_definition) : CreateQueryPreparer
    {
        /**
         * $field_definition format must be
         * $field_definition = [
         *  "field_name" => 'field',
         *  "field_type" => 'VARCHAR',
         *  "field_length" => 255, #optional
         *  "is_unique" => TRUE #optional
         *  "reference_table" => $table_name # for references
         * ]
         */
        $this->fields[] = $field_definition;
        return $this;
    }

    public function getQuery(): string
    {
        $constants = [];
        $references = [];
        
        $query = "CREATE TABLE `{$this->table_name}` ( ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
        foreach ($this->fields as $field) {
            $field["field_name"] = preg_replace("/[^a-z1-9_]+/", "", $field["field_name"]);
            $query .= ", `".$field["field_name"]."` ";
            if ($field["field_type"] === "VARCHAR") {
                $query.= "VARCHAR(".intval($field["field_length"]).")";
            } elseif (in_array($field["field_type"], ["INT", "DOUBLE", "TEXT", "DATE", "DATETIME", "TIME", "TINYTEXT", "LONGTEXT"])) {
                $query.= $field["field_type"];
            } elseif ($field["field_type"] == "MUL" && in_array($field["reference_table"], \CoreDB::database()::getTableList())) {
                $query.= "INT";
                array_push($references, [$field["field_name"], $field["reference_table"]]);
            } elseif($field["field_type"] == "ENUM"){
                $query.= "ENUM('".str_replace(",", "','", $field["list_values"])."')";
            } else {
                throw new Exception(Translation::getTranslation("check_wrong_fields"));
            }
            $query .= " COMMENT '{$field["comment"]}'";
            if (isset($field["is_unique"]) && $field["is_unique"] == 1) {
                array_push($constants, $field["field_name"]);
            }
        }
        $query .= ", created_at DATETIME DEFAULT CURRENT_TIMESTAMP, last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        foreach ($references as $reference) {
            $query.= ", FOREIGN KEY (`$reference[0]`) REFERENCES `$reference[1]`(ID) ";
        }
        foreach ($constants as $constant) {
            $query.= ", UNIQUE (`$constant`) ";
        }
        $query.= ") CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB COMMENT='{$this->table_comment}';";
        return $query;
    }
    
    public function execute() : PDOStatement
    {
        $result = parent::execute();
        //Migration::addMigration($this);
        return $result;
    }
}
