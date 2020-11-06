<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\DeleteQueryPreparerAbstract;

class DeleteQueryPreparer extends DeleteQueryPreparerAbstract
{
    /**
     * @inheritdoc
     */
    public function getQuery(): string
    {
        return "DELETE FROM `" . $this->table . "` " .
        $this->getCondition();
    }
    
    /**
     * @inheritdoc
     */
    public function condition(
        string $column,
        $value,
        string $operator = "=",
        string $connect = "AND"
    ): DeleteQueryPreparerAbstract {
        $placeholder = str_replace(".", "_", $column);
        $columnInfo = explode(".", $column);
        $column = $columnInfo[0];
        $fieldName = isset($columnInfo[1]) ? ".{$columnInfo[1]}" : "";
        $index = 0;
        while (isset($this->params[":$placeholder"])) {
            $placeholder = "{$column}_{$index}";
            $index++;
        }
        if (is_array($value)) {
            $condition = "(";
            foreach ($value as $index => $val) {
                $condition .= ($condition != "(" ? "," : "") . ":{$placeholder}_{$index}";
                $this->params[":{$placeholder}_{$index}"] = $val;
            }
            $condition .= ")";
        } elseif ($value === null) {
            $operator = "IS";
            $condition = "NULL";
        } else {
            $condition = ":$placeholder";
            $this->params[":$placeholder"] = $value;
        }
        $this->condition .= ($this->condition ? $connect : "") . " `$column`$fieldName $operator $condition ";
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCondition(): string
    {
        return $this->condition ? "WHERE " . $this->condition : "";
    }
}
