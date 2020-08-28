<?php

namespace CoreDB\Kernel\Database;

abstract class CreateQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected TableDefinition $definition;
    protected string $table_comment;
    protected array $fields = [];

    public bool $excludeForeignKeys;
    public function __construct(TableDefinition $definition, bool $excludeForeignKeys = false)
    {
        $this->definition = $definition;
        $this->excludeForeignKeys = $excludeForeignKeys;
    }
}
