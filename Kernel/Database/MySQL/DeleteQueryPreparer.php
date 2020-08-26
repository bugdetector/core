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
    public function getCondition() : string
    {
        return $this->condition ? "WHERE ".$this->condition : "";
    }
}
