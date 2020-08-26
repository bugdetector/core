<?php

namespace CoreDB\Kernel\Database;



abstract class InsertQueryPreparerAbstract extends QueryPreparerAbstract
{
    protected $fields;
    public function __construct($table, array $fields)
    {
        $this->table = $table;
        $this->fields = $fields;
    }
}
