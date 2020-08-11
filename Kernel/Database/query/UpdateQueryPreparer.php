<?php

namespace CoreDB\Kernel\Database;



class UpdateQueryPreparer extends QueryPreparer
{
    private $table;
    private $fields;
    private $condition;

    public function __construct(string $table, array $fields)
    {
        $this->table = $table;
        $this->fields = "";
        $this->params = array();
        $index = 0;
        foreach ($fields as $key => $field) {
            if ($field === "NULL" || !$field) {
                $field = null;
            } else {
                $field = \CoreDB::cleanXSS($field);
            }
            $this->fields .= ($index>0 ? ", ": "")." `$key` = :$key";
            $this->params[":".$key] = $field;
            $index++;
        }
    }


    public function getQuery() : string
    {
        return "UPDATE `".$this->table.
               "` SET ".$this->fields." ".$this->getCondition();
    }
    
    public function condition($condition, $params)
    {
        $this->condition = $condition;
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    private function getCondition()
    {
        return $this->condition ? "WHERE ".$this->condition : "";
    }
}
