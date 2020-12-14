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
        $column,
        $value = null,
        string $operator = "=",
        string $conjuction = "AND"
    ): SelectQueryPreparerAbstract {
        $this->condition->condition($column, $value, $operator, $conjuction);
        return $this;
    }
    
    private function getCondition()
    {
        return $this->condition->condition ? "WHERE " . $this->condition->condition : "";
    }
    
    private function getLimit()
    {
        return $this->limit ? "LIMIT " . $this->limit . ($this->offset ? " OFFSET " . $this->offset : "") : "";
    }
}
