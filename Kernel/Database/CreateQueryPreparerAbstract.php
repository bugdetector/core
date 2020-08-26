<?php

namespace CoreDB\Kernel\Database;


abstract class CreateQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected TableDefinition $definition;
    protected string $table_comment;
    protected array $fields = [];
    public function __construct(TableDefinition $definition)
    {
        $this->definition = $definition;
    }
}
