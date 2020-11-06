<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\UpdateQueryPreparerAbstract;

class UpdateQueryPreparer extends UpdateQueryPreparerAbstract
{
    public function getQuery(): string
    {
        return "UPDATE `" . $this->table .
               "` SET " . $this->getFields() . " " . $this->getCondition();
    }

    private function getFields(): string
    {
        $fields = "";
        $index = 0;
        foreach ($this->fields as $key => $field) {
            if ($field === "NULL" || (!is_numeric($field) && !$field)) {
                $field = null;
            }
            $fields .= ($index > 0 ? ", " : "") . " `$key` = :$key";
            $this->params[":" . $key] = $field;
            $index++;
        }
        return $fields;
    }
    
    /**
     * @inheritdoc
     */
    public function condition(
        string $column,
        $value,
        string $operator = "=",
        string $connect = "AND"
    ): UpdateQueryPreparerAbstract {
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

    public function getCondition(): string
    {
        return $this->condition ? "WHERE " . $this->condition : "";
    }
}
