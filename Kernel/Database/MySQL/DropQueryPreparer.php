<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\DropQueryPreparerAbstract;
use PDO;

/**
 * Prepares SQL Query For Drop Table and Column
 *
 * @author murat
 */
class DropQueryPreparer extends DropQueryPreparerAbstract
{
    public bool $dropl_foreing_keys = false;
    public string $query = "";
    public function getQuery(): string
    {
        if ($this->column) {
            $this->checkForeignKeyConstraints();
            $this->query .= "ALTER TABLE `$this->table` DROP COLUMN `$this->column`;";
        } else {
            $this->query .= "DROP TABLE `$this->table`;";
        }
        return $this->query;
    }

    public function checkForeignKeyConstraints()
    {
        $select_contsraints = MySQLDriver::getInstance()->select("information_schema.KEY_COLUMN_USAGE", "kcu", false)
        ->select("kcu", ["CONSTRAINT_NAME"])
        ->condition("REFERENCED_TABLE_SCHEMA", DB_NAME)
        ->condition("TABLE_NAME", $this->table)
        ->condition("COLUMN_NAME", $this->column);
        foreach ($select_contsraints->execute()->fetchAll(PDO::FETCH_COLUMN) as $constraint) {
            $this->query .= "ALTER TABLE `{$this->table}` DROP FOREIGN KEY `{$constraint}`;";
        }
    }
}
