<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\InsertQueryPreparerAbstract;

class InsertQueryPreparer extends InsertQueryPreparerAbstract
{

    /**
     * @inheritdoc
     */
    public function getQuery() : string
    {
        return "INSERT INTO `".
                $this->table."` ".
                $this->getBackQuery();
    }
    
    private function getBackQuery()
    {
        $fields = "( ";
        $values = "VALUES (";
        $this->params = [];
        $index = 0;
        foreach ($this->fields as $key => $field) {
            if($field === "NULL" || (!is_numeric($field) && !$field)){
                $field = null;
            } else {
                $field = \CoreDB::cleanXSS($field);
            }
            $fields.= ($index>0 ? ", `" : "`").$key."`";
            $values.= ($index>0 ? ", " : "")." ? ";
            $this->params[] = $field;
            $index++;
        }
        
        return $fields.") ".$values.")";
    }
}
