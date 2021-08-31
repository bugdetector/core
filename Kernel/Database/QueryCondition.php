<?php

namespace CoreDB\Kernel\Database;

class QueryCondition
{
    public QueryPreparerAbstract $query;
    public string $condition = "";
    public array $params = [];

    public function __construct(QueryPreparerAbstract $query)
    {
        $this->query = $query;
    }

    public function condition($column, $value = null, $operator = "=", $conjuction = "AND"): QueryCondition
    {
        if ($column instanceof QueryCondition) {
            if ($column->condition) {
                $this->condition .= ($this->condition ? $conjuction : "") . " ({$column->condition})";
            }
        } else {
            $columnInfo = explode(".", $column);
            $column = $columnInfo[0];
            $fieldName = isset($columnInfo[1]) ? ".{$columnInfo[1]}" : "";
            if (is_array($value)) {
                $condition = "(";
                foreach ($value as $val) {
                    $placeholder = $this->query->addParameter($column, $val);
                    $condition .= ($condition != "(" ? "," : "") . ":{$placeholder}";
                }
                $condition .= ")";
                $operator = "IN";
            } elseif ($value === null) {
                $operator = "IS";
                $condition = "NULL";
            } else {
                $placeholder = $this->query->addParameter($column, $value);
                $condition = ":$placeholder";
            }
            $this->condition .= ($this->condition != "" ? $conjuction : "") .
            " `$column`$fieldName $operator $condition ";
        }
        return $this;
    }
}
