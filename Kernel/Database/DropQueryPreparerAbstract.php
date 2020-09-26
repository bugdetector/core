<?php


namespace CoreDB\Kernel\Database;

/**
 * Prepares SQL Query For Drop Table and Column
 *
 * @author murat
 */
abstract class DropQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected $column;
    public function __construct(string $table, string $column = null)
    {
        $this->table = $table;
        $this->column = $column;
    }

    public function setColumn(string $column) : DropQueryPreparerAbstract
    {
        $this->column = $column;
        return $this;
    }
}
