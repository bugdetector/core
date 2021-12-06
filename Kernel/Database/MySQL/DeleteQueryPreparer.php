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
        $column,
        $value = null,
        string $operator = "=",
        string $conjuction = "AND"
    ): DeleteQueryPreparerAbstract {
        $this->condition->condition($column, $value, $operator, $conjuction);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCondition(): string
    {
        return $this->condition->condition ? "WHERE " . $this->condition->condition : "";
    }
}
