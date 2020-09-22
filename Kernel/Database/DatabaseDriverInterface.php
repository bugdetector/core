<?php

namespace CoreDB\Kernel\Database;

use CoreDB\Kernel\Database\AlterQueryPreparerAbstract;
use CoreDB\Kernel\Database\CreateQueryPreparerAbstract;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DeleteQueryPreparerAbstract;
use CoreDB\Kernel\Database\DropQueryPreparerAbstract;
use CoreDB\Kernel\Database\InsertQueryPreparerAbstract;
use CoreDB\Kernel\Database\QueryPreparerAbstract;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\Database\TruncateQueryPreparerAbstract;
use CoreDB\Kernel\Database\UpdateQueryPreparerAbstract;
use PDOStatement;

interface DatabaseDriverInterface
{

    const INTEGER = "integer";
    const FLOAT = "float";
    const CHECKBOX = "checkbox";
    const SHORT_TEXT = "short_text";
    const TEXT = "text";
    const LONG_TEXT = "long_text";
    const DATE = "date";
    const DATETIME = "datetime";
    const TIME = "time";
    const FILE = "file";
    const TABLE_REFERENCE = "table_reference";
    const ENUMARATED_LIST = "enumarated_list";

    /**
     * Check database connection and return result.
     * @return bool
     *  Database connection availability.
     */
    public static function checkConnection(string $dbServer, string $dbName, string $dbUsername, string $dbPassword) : bool;

    /**
     * Excecute Query
     * @param QueryPreparerAbstract $query
     *  Query object to execute
     * @return PDOStatement
     *  Result
     */
    public function execute(QueryPreparerAbstract $query): PDOStatement;

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
     * Select from a table
     * @param string $table_name
     *  Table name
     * @param string $alias
     *  Alias
     * @param bool $quote
     *  Use quote
     * @return SelectQueryPreparerAbstract
     *  Select query preparer
     */
    public function select(string $table_name, string $alias = "", bool $quote = true) : SelectQueryPreparerAbstract;

    /**
     * Insert new record to a table
     * @param string $table_name
     *  Table name
     * @param array $fields
     *  Fields set in key value pairs
     * @return InsertQueryPreparerAbstract
     *  Insert query preparer
     */
    public function insert(string $table_name, array $fields) : InsertQueryPreparerAbstract;

    /**
     * Update a record in a table
     * @param string $table_name
     *  Table name
     * @param array $fields
     *  Fields set in key value pairs
     * @return UpdateQueryPreparerAbstract
     *  Update query preparer
     */
    public function update(string $table_name, array $fields) : UpdateQueryPreparerAbstract;

    /**
     * Delete a record in a table
     * @param string $table_name
     *  Table name
     * @return DeleteQueryPreparerAbstract
     *  Delete query preparer
     */
    public function delete(string $table_name) : DeleteQueryPreparerAbstract;
    
    /**
     * Truncate a table
     * @param string $table_name
     *  Table name
     * @return TruncateQueryPreparerAbstract
     *  Truncate query preparer
     */
    public function truncate(string $table_name) : TruncateQueryPreparerAbstract;

    /**
     * Drop a table or a column
     * @param string $table_name
     *  Table name
     * @param string $column
     *  Column name
     * @return DropQueryPreparerAbstract
     *  Drop query preparer
     */
    public function drop(string $table_name, string $column = null) : DropQueryPreparerAbstract;

    /**
     * Create new table
     * @param TableDefinition $table
     *  Table name
     * @return CreateQueryPreparerAbstract
     *  Create query preparer
     */
    public function create(TableDefinition $table) : CreateQueryPreparerAbstract;

    /**
     * Alter table structure
     * @param TableDefinition $table
     *  Table name
     * @return AlterQueryPreparerAbstract
     *  Alter query preparer
     */
    public function alter(TableDefinition $table = null) : AlterQueryPreparerAbstract;

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
     * Return quoted string
     * Same as _quote() but this is static
     * @param string $string
     *  String to quote
     * @return string
     *  Quoted string
     */
    public static function quote(string $string): string;


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

    /**
     * @return array
     *  Data types
     */
    public static function dataTypes() : array;

    /**
     * @param DataTypeAbstract $dataType
     * Data Type
     * @return string
     * Database known name of field
     */
    public function getColumnDefinition(DataTypeAbstract $dataType) : string;

    /**
     * Return current timestamp method for default value
     * @return string
     * Current Timestamp
     */
    public function currentTimestamp(): string;

    /**
     * Return current timestamp on update method for default value
     * @return string
     * Current Timestamp
     */
    public function currentTimestampOnUpdate(): string;
}
