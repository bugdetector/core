<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\UpdateQueryPreparerAbstract;

class UpdateQueryPreparer extends UpdateQueryPreparerAbstract
{
    public function getQuery() : string
    {
        return "UPDATE `".$this->table.
               "` SET ".$this->getFields()." ".$this->getCondition();
    }

    private function getFields() : string
    {
        $fields = "";
        $index = 0;
        foreach ($this->fields as $key => $field) {
            if ($field === "NULL" || (!is_numeric($field) && !$field)) {
                $field = null;
            } else {
                $field = \CoreDB::cleanXSS($field);
            }
            $fields .= ($index>0 ? ", ": "")." `$key` = :$key";
            $this->params[":".$key] = $field;
            $index++;
        }
        return $fields;
    }
    
    /**
     * @inheritdoc
     */
    public function condition(string $column, $value, string $operator = "=", string $connect = "AND") : UpdateQueryPreparerAbstract
    {
        $placeholder = $column;
        $index = 0;
        while(isset($this->params[":$placeholder"])){
            $placeholder = "{$column}_{$index}";
        }
        $this->condition .= ($this->condition ? $connect : "")." `$column` $operator :$placeholder ";
        $this->params[":$placeholder"] = $value;
        return $this;
    }

    public function getCondition() : string
    {
        return $this->condition ? "WHERE ".$this->condition : "";
    }
}
