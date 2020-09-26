<?php
namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\DeleteQueryPreparerAbstract;

class DeleteQueryPreparer extends DeleteQueryPreparerAbstract
{
    /**
     * @inheritdoc
     */
    public function getQuery() : string
    {
        return "DELETE FROM `".$this->table."` ".
        $this->getCondition();
    }
    
    /**
     * @inheritdoc
     */
    public function condition(string $column, $value, string $operator = "=", string $connect = "AND") : DeleteQueryPreparerAbstract
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

    /**
     * @inheritdoc
     */
    public function getCondition() : string
    {
        return $this->condition ? "WHERE ".$this->condition : "";
    }
}
