<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\AlterQueryPreparerAbstract;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\DatabaseDriverInterface;
use Exception;
use PDOStatement;
use Src\Entity\Cache;
use Src\Entity\Translation;

class AlterQueryPreparer extends AlterQueryPreparerAbstract
{
    public DatabaseDriverInterface $db;
    public array $queries = [];

    public function __construct(TableDefinition $tableDefinition)
    {
        parent::__construct($tableDefinition);
        $this->db = \CoreDB::database();
        $differences = [];
        $original_description = $this->db->getTableDescription($this->table_definition->table_name);
        /**
         * @var DataTypeAbstract $dataType
         */
        $previous_column = "ID";
        foreach ($this->table_definition->fields as $column_name => $dataType) {
            if(in_array($column_name, ["ID", "created_at", "last_updated"])){
                continue;
            }
            else if (isset($original_description[$column_name])) {
                if (!$dataType->equals($original_description[$column_name])) {
                    $differences[] = [
                        "old" => $original_description[$column_name],
                        "new" => $dataType,
                        "after" => $previous_column
                    ];
                }
            } else {
                $differences[] = [
                    "old" => null,
                    "new" => $dataType,
                    "after" => $previous_column
                ];
            }
            $previous_column = $column_name;
        }
        if(empty($differences)){
            throw new Exception(Translation::getTranslation("no_change_on_table"));
        }
        foreach ($differences as $difference) {
            /**
             * @var DataTypeAbstract
             */
            $old_column = $difference["old"];
            /**
             * @var DataTypeAbstract
             */
            $new_column = $difference["new"];
            $after = $difference["after"];

            if(!$old_column || get_class($old_column) != get_class($new_column)){
                if($old_column){
                    $this->queries[] = $this->db->drop($this->table_definition->table_name, $difference["old"]->column_name)->getQuery();
                }
                $this->queries[] = "ALTER TABLE `{$this->table_definition->table_name}` ADD " . $this->db->getColumnDefinition($new_column) . " AFTER `{$after}`;";
                if ($new_column->isUnique) {
                    $this->queries[] = "ALTER TABLE `{$this->table_definition->table_name}` ADD UNIQUE(`{$new_column->column_name}`);";
                }
    
                if ($new_column instanceof TableReference) {
                    $this->queries[] = "ALTER TABLE `{$this->table_definition->table_name}` ADD FOREIGN KEY (`{$new_column->column_name}`) REFERENCES `{$new_column->reference_table}`(ID);";
                }
            }else{
                $this->queries[] = "ALTER TABLE `{$this->table_definition->table_name}` CHANGE `{$new_column->column_name}` ". $this->db->getColumnDefinition($new_column) . ";";
                if(!$old_column->isUnique && $new_column->isUnique){
                    $this->queries[] = "ALTER TABLE `{$this->table_definition->table_name}` ADD UNIQUE(`{$new_column->column_name}`);";
                }
                else if($old_column->isUnique && !$new_column->isUnique){
                    $this->queries[] = "ALTER TABLE `{$this->table_definition->table_name}` DROP INDEX `{$new_column->column_name}`;";
                }
            }
        }
        $this->queries[] = $this->db->truncate(Cache::TABLE)->getQuery();
    }
    public function getQuery(): string
    {
        return implode("\n", $this->queries);
    }

    public function execute(): PDOStatement
    {
        $result = parent::execute();
        return $result;
    }
}