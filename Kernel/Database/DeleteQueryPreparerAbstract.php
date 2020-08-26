<?php
namespace CoreDB\Kernel\Database;

abstract class DeleteQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected $condition;
    public function __construct($table)
    {
        $this->table = $table;
    }
    
    public function condition(string $condition, array $params = [])
    {
        $this->condition = $condition;
        $this->params = $params;
        return $this;
    }
    
    /**
     * Return condition string
     * @return string
     * Condition string
     */
    abstract function getCondition() : string;
}
