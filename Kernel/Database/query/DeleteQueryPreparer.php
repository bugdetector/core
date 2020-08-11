<?php
namespace CoreDB\Kernel\Database;

class DeleteQueryPreparer extends QueryPreparer
{
    private $table;
    private $condition;


    public function __construct($table)
    {
        $this->table = $table;
    }

    public function getQuery() : string
    {
        return "DELETE FROM `".$this->table."` ".
        $this->getCondition();
    }
    
    public function condition(string $condition, array $params = [])
    {
        $this->condition = $condition;
        $this->params = $params;
        return $this;
    }
    
    private function getCondition()
    {
        return $this->condition ? "WHERE ".$this->condition : "";
    }
}
