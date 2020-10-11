<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB\Kernel\Database\TruncateQueryPreparerAbstract;

class TruncateQueryPreparer extends TruncateQueryPreparerAbstract
{
    public function __construct(string $table)
    {
        $this->table = $table;
    }


    public function getQuery(): string
    {
        return "TRUNCATE TABLE `{$this->table}`;";
    }
}
