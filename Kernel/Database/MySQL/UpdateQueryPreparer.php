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
        string $operator = null,
        string $connect = "AND"
    ): UpdateQueryPreparerAbstract {
        $this->condition->condition($column, $value, $operator, $connect);
        return $this;
    }

    public function getCondition(): string
    {
        return $this->condition->condition ? "WHERE " . $this->condition->condition : "";
    }
}
