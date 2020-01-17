<?php

/**
 * Prepares SQL Query For Drop Table and Column
 *
 * @author murat
 */
class DropQueryPreparer extends CoreDBQueryPreparer {
    private $table_name;
    private $column;
    public function __construct(string $table_name, string $column = NULL){
        $this->table_name = $table_name;
        $this->column = $column;
    }

    public function setColumn(string $column) : DropQueryPreparer {
       $this->column = $column;
       return $this;
    }

    public function getQuery(): string {
        if($this->column){
            return "ALTER TABLE `$this->table_name` DROP COLUMN `$this->column`";
        }else{
            return "DROP TABLE `$this->table_name`";
        }
    }
    
    public function execute() : PDOStatement {
        $result = parent::execute();
        Migration::addMigration($this);
        return $result;
    }

}
