<?php

namespace CoreDB\Kernel\Database;

abstract class UpdateQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected array $fields = [];
    protected string $condition = "";

    public function __construct(string $table, array $fields)
    {
        $this->table = $table;
        $this->fields = $fields;
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
     * @return UpdateQueryPreparerAbstract
     *  Self
     */
    abstract public function condition(
        string $column,
        $value,
        string $operator = "=",
        string $connect = "AND"
    ): UpdateQueryPreparerAbstract;
    
    abstract public function getCondition(): string;
}
