<?php

namespace CoreDB\Kernel;

use CoreDB\Kernel\Database\QueryPreparer;
use PDOStatement;

interface DatabaseDriver{

    /**
     * Excecute Query
     * @param QueryPreparer $query
     *  Query object to execute
     * @return PDOStatement
     *  Result
     */
    public function execute(QueryPreparer $query): PDOStatement;

    /**
     * Run query by string
     * @param string $query
     *  Raw query
     * @param array $params
     *  Parameters
     * @return PDOStatement
     *  Result
     */
    public function query(string $query, array $params = null): PDOStatement;

    /**
     * Begin transaction
     */
    public function beginTransaction();

    /**
     * Rollback transaction
     */
    public function rollback();

    /**
     * Commit trasaction
     */
    public function commit();

    /**
     * Return last insterted ID
     * @return int
     *  Last insert ID
     */
    public function lastInsertId(): int;

    /**
     * Return quoted string
     * @param string $string
     *  String to quote
     * @return string
     *  Quoted string
     */
    public function _quote(string $string) : string;


    /**
     * Return table list
     * @return array
     */
    public static function getTableList(): array;

    /**
     * Returns table comment
     * @param string $table_name
     *  Table name
     * @return string
     *  Table comment
     */
    public static function getTableComment(string $table_name): string;

    /**
     * Returns column comment
     * @param string $table_name
     *  Table name
     * @param string $column_name
     *  Column name
     * @return string
     *  Column comment
     */
    public static function getColumnComment(string $table_name, string $column_name): string;

    /**
     * Returns table description
     * @param string $table_name
     *  Table name
     * @return array
     *  Table Description
     */
    public static function getTableDescription(string $table_name): array;
    

    /**
     * Returns foreign keys referenced to table
     * @param string $table_name
     *  Table name
     * @return array
     *  Reference list
     */
    public static function getReferencesToTable(string $table_name): array;

    /**
     * Returns foreign keys on table
     * @param string $table_name
     *  Table name
     * @return PDOStatement
     *  References
     */
    public static function getTableReferences(string $table_name): PDOStatement;

    /**
     * Returns all foreign key descriptions in database
     * @return PDOStatement 
     * All defined references in database
     */
    public static function getAllTableReferences(): PDOStatement;


    /**
     * Returns true if unique column is also a foreign key else returns false
     * @param string $table_name
     *  Table name
     * @param string $uni
     *  Column name
     * @return bool
     *  Unique key is also a foreign key
     */
    public static function isUniqueForeignKey(string $table, string $uni): bool;


    /**
     * Returns foreign key description
     * @param string $table_name
     *  Table name
     * @param string $foreignKey
     *  Foreign key column
     * @return array
     *  Foreign key description
     */
    public static function getForeignKeyDescription(string $table_name, string $foreignKey): array;
}