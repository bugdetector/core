<?php

namespace CoreDB\Kernel\Database;



class TruncateQueryPreparer extends QueryPreparer
{
    private $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }


    public function getQuery() : string
    {
        return "TRUNCATE TABLE `{$this->table}`";
    }
}
