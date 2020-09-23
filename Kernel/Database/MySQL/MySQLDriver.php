<?php

namespace CoreDB\Kernel\Database\MySQL;

use CoreDB;
use CoreDB\Kernel\Database\AlterQueryPreparerAbstract;
use CoreDB\Kernel\Database\CreateQueryPreparerAbstract;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\Date;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\DataType\Time;
use CoreDB\Kernel\Database\DeleteQueryPreparerAbstract;
use CoreDB\Kernel\Database\DropQueryPreparerAbstract;
use CoreDB\Kernel\Database\InsertQueryPreparerAbstract;
use CoreDB\Kernel\Database\QueryPreparerAbstract;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\Database\TruncateQueryPreparerAbstract;
use CoreDB\Kernel\Database\UpdateQueryPreparerAbstract;
use CoreDB\Kernel\Database\DatabaseDriver;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DatabaseInstallationException;
use \PDO;
use \PDOException;
use \PDOStatement;

class MySQLDriver extends DatabaseDriver
{
    private static $instance;
    private ?PDO $connection = null;

    private function __construct(string $dbServer, string $dbName, string $dbUsername, string $dbPassword)
    {
        self::$instance = $this;
        $this->connection = new PDO("mysql:host=" . $dbServer . ";dbname=" . $dbName, $dbUsername, $dbPassword);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $this->connection->query("SET NAMES UTF8");
    }

    /**
     * @inheritdoc
     */
    public static function checkConnection(string $dbServer, string $dbName, string $dbUsername, string $dbPassword) : bool{
        try {
            new PDO("mysql:host=" . $dbServer . ";dbname=" . $dbName, $dbUsername, $dbPassword);
            return true;
        } catch (PDOException $ex) {
            return false;
        }
    }

    /**
     *
     * @return MySQLDriver
     */
    public static function getInstance(): MySQLDriver
    {
        if (self::$instance == null) {
            if(defined("DB_SERVER") && defined("DB_NAME") && defined("DB_USER") && defined("DB_PASSWORD")){
                return new self(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
            }else{
                throw new DatabaseInstallationException("Missing database configuration.");
            }
        }
        return self::$instance;
    }

    /**
     *
     * @param QueryPreparerAbstract $query
     * @return PDOStatement
     */
    public function execute(QueryPreparerAbstract $query): PDOStatement
    {
        try {
            if(!$this->connection){
                throw new DatabaseInstallationException("Database connection not provided.");
            }
            $statement = $this->connection->prepare($query->getQuery());
            $statement->execute($query->getParams());
            return $statement;
        } catch (PDOException $ex) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            if($ex->getCode() == "42S02"){
                throw new DatabaseInstallationException($ex->getMessage());
            }
            throw $ex;
        }
    }

    /**
     * @inheritdoc
     */
    public function query(string $query, array $params = null): PDOStatement
    {
        try {
            return $this->connection->query($query);
        } catch (PDOException $ex) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $ex;
        }
    }

    /**
     * @inheritdoc
     */
    public function select(string $table_name, string $alias = "", bool $quote = true): SelectQueryPreparerAbstract
    {
        return new SelectQueryPreparer($table_name, $alias, $quote);
    }

    /**
     * @inheritdoc
     */
    public function insert(string $table_name, array $fields): InsertQueryPreparerAbstract
    {
        return new InsertQueryPreparer($table_name, $fields);
    }

    /**
     * @inheritdoc
     */
    public function update(string $table_name, array $fields): UpdateQueryPreparerAbstract
    {
        return new UpdateQueryPreparer($table_name, $fields);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $table_name): DeleteQueryPreparerAbstract
    {
        return new DeleteQueryPreparer($table_name);
    }

    /**
     * @inheritdoc
     */

    public function truncate(string $table_name): TruncateQueryPreparerAbstract
    {
        return new TruncateQueryPreparer($table_name);
    }

    /**
     * @inheritdoc
     */
    public function drop(string $table_name, ?string $column = null): DropQueryPreparerAbstract
    {
        return new DropQueryPreparer($table_name, $column);
    }

    /**
     * @inheritdoc
     */
    public function create(TableDefinition $table, bool $excludeForeignKeys = false) : CreateQueryPreparerAbstract
    {
        return new CreateQueryPreparer($table, $excludeForeignKeys);
    }

    /**
     * @inheritdoc
     */
    public function alter(TableDefinition $table = null) : AlterQueryPreparerAbstract
    {
        return new AlterQueryPreparer($table);
    }

    public function rollback()
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        if ($this->connection->inTransaction()) {
            $this->connection->commit();
        }
    }

    /**
     * @inheritdoc
     */
    public function lastInsertId(): int
    {
        return $this->connection->lastInsertId();
    }

    public static function quote(string $string): string
    {
        return self::getInstance()->_quote($string);
    }

    /**
     * @inheritdoc
     */
    public function _quote(string $string): string
    {
        return $this->connection->quote($string);
    }

    /**
     * @inheritdoc
     */
    public static function getTableDescription(string $table): array
    {
        $descriptions = self::getInstance()->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $fields = [];
        foreach ($descriptions as $index => $description) {
            $field = null;
            $type = substr($description["Type"], 0, strpos($description["Type"], "(") ?: strlen($description["Type"]));
            if ($type == "int" && ($description["Key"] == "MUL" ||
                ($description["Key"] == "UNI" && self::isUniqueForeignKey($table, $description["Field"])))) {
                $type = "table_reference";
            }
            switch ($type) {
                case "int":
                    $field = new Integer($description["Field"]);
                    break;
                case "double":
                    $field = new FloatNumber($description["Field"]);
                    break;
                case "tinyint":
                    $field = new Checkbox($description["Field"]);
                    break;
                case "varchar":
                    $field = new ShortText($description["Field"]);
                    $field->length = filter_var($description["Type"], FILTER_SANITIZE_NUMBER_INT);
                    break;
                case "text":
                    $field = new Text($description["Field"]);
                    break;
                case "longtext":
                    $field = new LongText($description["Field"]);
                    break;
                case "date":
                    $field = new Date($description["Field"]);
                    break;
                case "datetime":
                    $field = new DateTime($description["Field"]);
                    break;
                case "time":
                    $field = new Time($description["Field"]);
                    break;
                case "table_reference":
                    $fk_description = self::getForeignKeyDescription($table, $description["Field"]);
                    $reference_table = !empty($fk_description) ? $fk_description["REFERENCED_TABLE_NAME"] : "";
                    if($reference_table == "files"){
                        $field = new File($description["Field"]);
                    }else{
                        $field = new TableReference($description["Field"]);
                        $field->reference_table = $reference_table;
                    }
                    break;
                case "enum":
                    $field = new EnumaratedList($description["Field"]);
                    preg_match("/^enum\(\'(.*)\'\)$/", $description["Type"], $matches);
                    $options = [];
                    foreach (explode("','", $matches[1]) as $option) {
                        $options[$option] = $option;
                    }
                    $field->values = $options;
                    break;
            }
            if ($description["Key"] == "UNI") {
                $field->isUnique = true;
            }
            if ($description["Null"] == "NO") {
                $field->isNull = false;
            }
            $field->comment = CoreDB::database()->getColumnComment($table, $description["Field"]);
            $fields[$field->column_name] = $field;
        }
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public static function getReferencesToTable(string $table): array
    {
        return CoreDB::database()->select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", false)
            ->select("", ["TABLE_NAME", "COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA", DB_NAME)
            ->condition("REFERENCED_TABLE_NAME", $table)
            ->execute()->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * @inheritdoc
     */
    public static function getTableReferences(string $table): PDOStatement
    {
        return CoreDB::database()->select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", false)
            ->select("", ["REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA", DB_NAME)
            ->condition("TABLE_NAME", $table)->execute();
    }

    /**
     * @inheritdoc
     */
    public static function getAllTableReferences(): PDOStatement
    {
        return CoreDB::database()->select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", false)
            ->select("", ["TABLE_NAME", "COLUMN_NAME", "REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA", DB_NAME)
            ->execute();
    }

    /**
     * @inheritdoc
     */
    public static function isUniqueForeignKey(string $table, string $uni): bool
    {
        return count(self::getForeignKeyDescription($table, $uni)) !== 0;
    }

    /**
     * @inheritdoc
     */
    public static function getForeignKeyDescription(string $table, string $foreignKey): array
    {
        $result = CoreDB::database()->select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", false)
            ->select("", ["REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA", DB_NAME)
            ->condition("TABLE_NAME", $table)
            ->condition("COLUMN_NAME", $foreignKey)
            ->execute()->fetch(PDO::FETCH_BOTH);
        return $result ?: [];
    }

    /**
     *
     * @return array
     */
    public static function getTableList(): array
    {
        $results = self::getInstance()->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
        $tables = [];
        foreach ($results as $result) {
            $tables[$result[0]] = $result[0];
        }
        return $tables;
    }

    /**
     * @inheritdoc
     */
    public static function getTableComment(string $table_name): string
    {
        return CoreDB::database()->select("INFORMATION_SCHEMA.TABLES", "", false)
            ->condition("table_schema", DB_NAME)
            ->condition("table_name", $table_name)
            ->select("", ["table_comment AS comment"])
            ->execute()->fetchObject()->comment;
    }


    /**
     * @inheritdoc
     */
    public static function getColumnComment(string $table_name, string $column_name): string
    {
        $comment_result = CoreDB::database()->select("INFORMATION_SCHEMA.COLUMNS", "", false)
            ->condition("TABLE_SCHEMA", DB_NAME)
            ->condition("TABLE_NAME", $table_name)
            ->condition("COLUMN_NAME", $column_name)
            ->select("", ["COLUMN_COMMENT"])
            ->execute()->fetchObject();
        if ($comment_result) {
            $comment = strval($comment_result->COLUMN_COMMENT);
        } else {
            $comment = "";
        }
        return $comment;
    }

    public function getColumnDefinition(DataTypeAbstract $dataType): string
    {
        $type_description = "`$dataType->column_name` ";
        $class_name = get_class($dataType);
        if ($class_name == Integer::class) {
            $type_description .= "INT";
        } elseif ($class_name == FloatNumber::class) {
            $type_description .= "DOUBLE";
        } elseif ($class_name == Checkbox::class) {
            $type_description .= "BOOLEAN";
        } elseif ($class_name == ShortText::class) {
            /**
             * @var ShortText $dataType
             */
            $type_description .= "VARCHAR({$dataType->length})";
        } elseif ($class_name == Text::class) {
            $type_description .= "TEXT";
        } elseif ($class_name == LongText::class) {
            $type_description .= "LONGTEXT";
        } elseif ($class_name == Date::class) {
            $type_description .= "DATE";
        } elseif ($class_name == DateTime::class) {
            $type_description .= "DATETIME";
        } elseif ($class_name == Time::class) {
            $type_description .= "TIME";
        } elseif ($class_name == File::class) {
            $type_description .= "INT";
        } elseif ($class_name == TableReference::class) {
            $type_description .= "INT";
        } elseif ($class_name == EnumaratedList::class) {
            $type_description .= "ENUM('".implode("','", $dataType->values)."')";
        }
        if (!$dataType->isNull) {
            $type_description .= " NOT NULL";
        }
        if ($dataType->autoIncrement) {
            $type_description .= " AUTO_INCREMENT";
        }
        if ($dataType->primary_key) {
            $type_description .= " PRIMARY KEY";
        }
        if ($dataType->default) {
            $type_description .= " DEFAULT {$dataType->default}";
        }
        $type_description .= " COMMENT ".\CoreDB::database()->quote($dataType->comment);
        return $type_description;
    }

    /**
     * @inheritdoc
     */
    public function currentTimestamp() : string
    {
        return "CURRENT_TIMESTAMP";
    }

    /**
     * @inheritdoc
     */
    public function currentTimestampOnUpdate(): string
    {
        return "CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    }
}
