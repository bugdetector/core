<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;

class SelectQueryPreparer extends SelectQueryPreparerAbstract
{

    public function getQuery(): string
    {
        return "SELECT " . $this->distinct .
                $this->getFields() . " FROM " .
                $this->getTables() . " " .
                $this->getCondition() . " " .
                $this->getGroupBy() . " " .
                $this->getHaving() . " " .
                $this->getOrderBy() . " " .
                $this->getLimit();
    }
    
    /**
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = "DISTINCT ";
        return $this;
    }


    private function getTables()
    {
        $tables = "";
        $index = 0;
        foreach ($this->tables as $table) {
            if ($this->quote) {
                $table["tableName"] = "`{$table["tableName"]}`";
            }
            $tables .= (isset($table["join"]) ? $table["join"] . " JOIN " : " ") .
            $table["tableName"] . ($table["alias"] ? " AS " . $table["alias"] : " ")
            . (isset($table["on"]) && $table["on"] ? " ON " . $table["on"] . " " : " ");
            $index++;
        }
        return $tables;
    }
    
    /**
     * @inheritdoc
     */
    public function select($table, array $fields)
    {
        if (!$this->fields) {
            $this->fields = array();
        }
        foreach ($fields as $field) {
            array_push($this->fields, ($table ? $table . "." : "") . $field);
        }
        return $this;
    }
    
    private function getFields()
    {
        if (!$this->fields) {
            return "*";
        }
        $index = 0;
        $fields = "";
        foreach ($this->fields as $field) {
            $fields .= ($index > 0 ? ", " : "") . $field;
            $index++;
        }
        return $fields;
    }
    
    private function getOrderBy()
    {
        return $this->orderBy ? "ORDER BY " . $this->orderBy : "";
    }

    private function getGroupBy()
    {
        return $this->groupBy ? "GROUP BY $this->groupBy" : "";
    }

    private function getHaving()
    {
        return $this->having ? "HAVING $this->having" : "";
    }
    
    /**
     * @inheritdoc
     */
    public function condition(
        string $column,
        $value,
        string $operator = "=",
        string $connect = "AND"
    ): SelectQueryPreparerAbstract {
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
    
    private function getCondition()
    {
        return $this->condition ? "WHERE " . $this->condition : "";
    }
    
    private function getLimit()
    {
        return $this->limit ? "LIMIT " . $this->limit . ($this->offset ? " OFFSET " . $this->offset : "") : "";
    }
}
