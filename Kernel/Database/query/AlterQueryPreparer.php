<?php
namespace CoreDB\Kernel\Database;


use Exception;
use PDOStatement;
use Src\Entity\Translation;

class AlterQueryPreparer extends QueryPreparer
{
    private $table;
    private $query;

    public function __construct(string $table)
    {
        if (!in_array($table, \CoreDB::database()::getTableList())) {
            throw new Exception(Translation::getTranslation(67));
        }
        $this->table = $table;
    }
    
    public function addField(array $field) : AlterQueryPreparer
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
        $this->query = "ALTER TABLE `$this->table` ADD `".$field['field_name']."` ";
        if ($field["field_type"] === "VARCHAR") {
            $this->query.= "VARCHAR(".intval($field["field_length"]).") CHARACTER SET utf8 COLLATE utf8_general_ci;";
        } elseif (in_array($field["field_type"], ["INT", "DOUBLE", "TEXT", "DATE", "DATETIME", "TIME", "TINYTEXT", "LONGTEXT"])) {
            $this->query.= $field["field_type"].";";
        } elseif ($field["field_type"] == "MUL" && in_array($field["reference_table"], \CoreDB::database()::getTableList())) {
            $this->query .= "INT; ";
            $this->query .= "ALTER TABLE $this->table ADD FOREIGN KEY (`".$field["field_name"]."`) REFERENCES ".$field["reference_table"]."(ID)";
        } else {
            throw new Exception(Translation::getTranslation("check_wrong_fields"));
        }
        if (isset($field["is_unique"]) && $field["is_unique"] == 1) {
            $this->query .= "ALTER TABLE $this->table ADD UNIQUE(`".$field['field_name']."`);";
        }
        return $this;
    }


    public function getQuery(): string
    {
        return $this->query;
    }
    
    public function execute() : PDOStatement
    {
        $result = parent::execute();
        //Migration::addMigration($this);
        return $result;
    }
}
