<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB;
use CoreDB\Kernel\Database\CreateQueryPreparerAbstract;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\TableReference;
use PDOStatement;
use Src\Entity\Cache;

class CreateQueryPreparer extends CreateQueryPreparerAbstract
{
    public function getQuery(): string
    {
        $query = "CREATE TABLE `{$this->definition->table_name}` ( ";
        $db = CoreDB::database();
        $fields_query = [];
        $unique_query = [];
        $references_query = [];
        /**
         * @var DataTypeAbstract
         */
        foreach ($this->definition->fields as $field) {
            $fields_query[] = $db->getColumnDefinition($field);
            if ($field->isUnique) {
                $unique_query[] = "UNIQUE(`{$field->column_name}`)";
            }
            if (!$this->excludeForeignKeys && ($field instanceof TableReference)) {
                /**
                 * @var TableReference $field
                 */
                $references_query[] = "FOREIGN KEY (`{$field->column_name}`) REFERENCES `{$field->reference_table}`(ID)";
            }
        }
        $query .= implode(",\n", $fields_query);
        $query .= !empty($unique_query)? ",\n".implode(",\n", $unique_query) : "";
        $query .= !empty($references_query)? ",\n".implode(",\n", $references_query) : "";
        $query .= "\n) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB COMMENT ".\CoreDB::database()->quote($this->definition->table_comment).";";
        return $query;
    }

    public function execute(): PDOStatement
    {
        $result = parent::execute();
        Cache::clear();
        return $result;
    }
}
