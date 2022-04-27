<?php

namespace CoreDB\Kernel\Database;

abstract class DeleteQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected $condition;
    public function __construct($table)
    {
        $this->table = $table;
        $this->condition = new QueryCondition($this);
    }

     /**
     * Add condition to query
     * @param string $column
     *  Column name.
     * @param mixed $value
     *  Matching value.
     * @param string $operator
     *  Matching operator.
     * @param string $connect
     *  AND - OR vs. Default: AND
     * @return DeleteQueryPreparerAbstract
     *  Self
     */
    abstract public function condition(
        string $column,
        $value,
        string $operator = null,
        string $connect = "AND"
    ): DeleteQueryPreparerAbstract;

    /**
     * Return condition string
     * @return string
     * Condition string
     */
    abstract public function getCondition(): string;
}
