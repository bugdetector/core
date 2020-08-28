<?php

namespace CoreDB\Kernel\Database;

abstract class TruncateQueryPreparerAbstract extends QueryPreparerAbstract
{
    public function __construct(string $table)
    {
        $this->table = $table;
    }
}
