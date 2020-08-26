<?php

namespace CoreDB\Kernel\Database;


abstract class AlterQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected TableDefinition $table_definition;

    public function __construct(TableDefinition $table_definition)
    {
        $this->table_definition = $table_definition;
    }
}
