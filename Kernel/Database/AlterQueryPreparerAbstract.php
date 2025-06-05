<?php

namespace CoreDB\Kernel\Database;

abstract class AlterQueryPreparerAbstract extends QueryPreparerAbstract
{
    public array $queries = [];
    public array $foreignKeyQueries = [];
    public DatabaseDriverInterface $db;

    public function __construct(?TableDefinition $tableDefinition = null)
    {
        $this->db = \CoreDB::database();
        if ($tableDefinition) {
            $this->addTableDefinition($tableDefinition);
        }
    }

    /**
     * Add new table definition to query
     * @param TableDefinition $tableDefinition
     *  Table definition.
     */
    abstract public function addTableDefinition(TableDefinition $tableDefinition);
}
