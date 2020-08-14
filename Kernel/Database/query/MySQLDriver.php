<?php

namespace CoreDB\Kernel\Database;

use CoreDB\Kernel\DatabaseDriver;
use \PDO;
use \PDOException;
use \PDOStatement;
use Src\Entity\Cache;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\SelectWidget;
use Src\Form\Widget\TextareaWidget;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

/**
 * @property \PDO $connection
 */
class MySQLDriver implements DatabaseDriver
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        try {
            self::$instance = $this;
            $this->connection = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->query("SET NAMES UTF8");
        } catch (PDOException $ex) {
            die("Can't connect to database.");
        }
    }
    /**
     *
     * @return MySQLDriver
     */
    public static function getInstance(): MySQLDriver
    {
        if (self::$instance == null) {
            return new self();
        }
        return self::$instance;
    }

    /**
     *
     * @param QueryPreparer $query
     * @return PDOStatement
     */
    public function execute(QueryPreparer $query): PDOStatement
    {
        try {
            $statement = $this->connection->prepare($query->getQuery());
            $statement->execute($query->getParams());
            return $statement;
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
    public function query(string $query, array $params = NULL): PDOStatement
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

    public static function quote(string $string) : string
    {
        return self::getInstance()->_quote($string);
    }

    public function _quote(string $string) : string
    {
        return $this->connection->quote($string);
    }

    public static function get_supported_data_types(): array
    {
        return [
            "INT" => [
                "value" => "INT",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && $definition["Key"] != "MUL" && strpos($definition["Type"], "int") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $field = new InputWidget($desc["Field"]);
                    $field->setType("number");
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    return $field;
                }
            ],
            "DOUBLE" => [
                "value" => "DOUBLE",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "double") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $field = new InputWidget($desc["Field"]);
                    $field->setType("number");
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    $field->addAttribute("step", "0.01");
                    return $field;
                }
            ],
            "VARCHAR" => [
                "value" => "VARCHAR",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "varchar") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $field = new InputWidget($desc["Field"]);
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    return $field;
                }
            ],
            "TEXT" => [
                "value" => "TEXT",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "text") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $field = new TextareaWidget($desc["Field"]);

                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    $field->setLabel($desc["Field"]);
                    return $field;
                }
            ],
            "LONGTEXT" => [
                "value" => "LONGTEXT",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "longtext") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $field = new TextareaWidget($desc["Field"]);
                    $field->addClass("summernote");
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    return $field;
                }
            ],
            "DATE" => [
                "value" => "DATE",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "date") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $field = new InputWidget($desc["Field"]);
                    $field->addClass("dateinput datetimepicker-input");
                    $field->addAttribute("id", $desc["Field"]);
                    $field->addAttribute("data-target", "#" . $desc["Field"]);
                    $field->addAttribute("data-toggle", "datetimepicker");
                    $field->addAttribute("autocomplete", "off");
                    $field->setValue($object ? get_field_from_object($object, $desc["Field"]) : \CoreDB::get_current_date());
                    return $field;
                }
            ],
            "DATETIME" => [
                "value" => "DATETIME",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "datetime") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $field = new InputWidget($desc["Field"]);
                    $field->addClass("datetimeinput datetimepicker-input");
                    $field->addAttribute("id", $desc["Field"]);
                    $field->addAttribute("data-target", "#" . $desc["Field"]);
                    $field->addAttribute("data-toggle", "datetimepicker");
                    $field->addAttribute("autocomplete", "off");
                    $field->setValue($object ? get_field_from_object($object, $desc["Field"]) : \CoreDB::get_current_date());
                    return $field;
                }
            ],
            "TIME" => [
                "value" => "TIME",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "time") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $field = new InputWidget($desc["Field"]);
                    $field->addClass("timeinput datetimepicker-input");
                    $field->addAttribute("id", $desc["Field"]);
                    $field->addAttribute("data-target", "#" . $desc["Field"]);
                    $field->addAttribute("data-toggle", "datetimepicker");
                    $field->addAttribute("autocomplete", "off");
                    $field->setValue($object ? get_field_from_object($object, $desc["Field"]) : \CoreDB::get_current_date());
                    return $field;
                }
            ],
            "TINYTEXT" => [
                "value" => "FILE",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "tinytext") === 0 ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc) {
                    $file_name = $object ? get_field_from_object($object, $desc["Field"]) : "";
                    $field = InputWidget::create($desc["Field"])
                    ->removeClass("form-control")
                    ->addClass("w-100")
                    ->setType("file");
                    if ($file_name) {
                        $field->setDescription(
                            ViewGroup::create("a", "lead d-block")
                                ->addAttribute("href", BASE_URL . "/files/uploaded/{$object->table}/{$desc["Field"]}/{$file_name}")
                                ->addField(
                                    TextElement::create($file_name)
                                )
                        );
                    }
                    return $field;
                }
            ],
            "MUL" => [
                "value" => "REFERENCE",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Key"], "MUL") !== false ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc, $table) {
                    $fk_description = self::getForeignKeyDescription($table, $desc["Field"]);
                    $table_description = self::getTableDescription($fk_description["REFERENCED_TABLE_NAME"]);;
                    $entries = (new SelectQueryPreparer($fk_description[0]))->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
                    $options = [];
                    foreach ($entries as $entry) {
                        $options[$entry[0]] = $entry[1];
                    }
                    $field = new SelectWidget($desc["Field"]);
                    $field->setOptions($options)
                        ->setValue(get_field_from_object($object, $desc["Field"]))
                        ->addClass("autocomplete")
                        ->addAttribute("data-reference-table", $fk_description["REFERENCED_TABLE_NAME"])
                        ->addAttribute("data-reference-column", $table_description[1]["Field"]);
                    return $field;
                }
            ],
            "ENUM" => [
                "value" => "LIST",
                "selected_callback" => function ($definition) {
                    return [
                        "checked" => $definition && strpos($definition["Type"], "enum(") !== false ? "selected" : "",
                    ];
                },
                "input_field_callback" => function ($object, $desc, $table) {
                    $options = self::getInstance()->getEnumValues($table, $desc["Field"]);
                    foreach($options as &$option){
                        $option = Translation::getTranslation($option);
                    }
                    $field = new SelectWidget($desc["Field"]);
                    $field->setOptions($options)
                    ->addClass("autocomplete");
                    return $field;
                }
            ]
        ];
    }

    public function getEnumValues(string $table_name, string $column){
        $cache = Cache::getByBundleAndKey("enum_values", "{$table_name}-{$column}");
        if($cache){
            return $cache->value ? json_decode($cache->value, true) : [];
        }else{
            $result = $this->query(
                "SHOW COLUMNS FROM {$table_name} WHERE Field = '{$column}'"
            )->fetch(PDO::FETCH_ASSOC);
            preg_match("/^enum\(\'(.*)\'\)$/", $result["Type"], $matches);
            $options = [];
            foreach(explode("','", $matches[1]) as $option){
                $options[$option] = $option;
            }
            Cache::set("enum_values", "{$table_name}-{$column}", json_encode($options));
            return $options;
        }
    }

    /**
     * @inheritdoc
     */
    public static function getTableDescription(string $table): array
    {
        $cache = Cache::getByBundleAndKey("table_description", $table);
        if ($cache) {
            return json_decode($cache->value, true);
        } else {
            $descriptions = self::getInstance()->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($descriptions as $index => $desc) {
                if ($desc["Key"] == "UNI" && self::isUniqueForeignKey($table, $desc["Field"])) {
                    $descriptions[$index]["Key"] = "MUL-UNI";
                }
            }
            Cache::set("table_description", $table, json_encode($descriptions));
            return $descriptions;
        }
    }

    /**
     * @inheritdoc
     */
    public static function getReferencesToTable(string $table): array
    {
        return (new SelectQueryPreparer("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", false))
            ->select("", ["TABLE_NAME", "COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND REFERENCED_TABLE_NAME = :table")
            ->params(["scheme" => DB_NAME, "table" => $table])->execute()->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * @inheritdoc
     */
    public static function getTableReferences(string $table): PDOStatement
    {
        return (new SelectQueryPreparer("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", false))
            ->select("", ["REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND TABLE_NAME = :table")
            ->params(["scheme" => DB_NAME, "table" => $table])->execute();
    }

    /**
     * Returns all foreign key descriptions in database
     * @return PDOStatement All defined references in database
     */
    public static function getAllTableReferences(): PDOStatement
    {
        return (new SelectQueryPreparer("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", false))
            ->select("", ["TABLE_NAME", "COLUMN_NAME", "REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme")
            ->params(["scheme" => DB_NAME])->execute();
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
        $cache = Cache::getByBundleAndKey("foreign_key_description", $table . $foreignKey);
        if ($cache) {
            return json_decode($cache->value, true) ?: [];
        } else {
            $result = (new SelectQueryPreparer("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", false) )
                ->select("", ["REFERENCED_TABLE_NAME", "REFERENCED_COLUMN_NAME"])
                ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND TABLE_NAME = :table AND COLUMN_NAME = :column")
                ->params(["scheme" => DB_NAME, "table" => $table, ":column" => $foreignKey])->execute()->fetch(PDO::FETCH_BOTH);
            if ($result) {
                Cache::set("foreign_key_description", $table . $foreignKey, json_encode($result));
            }
            return $result ?: [];
        }
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
        return  (new SelectQueryPreparer("INFORMATION_SCHEMA.TABLES", "", false))
            ->condition("table_schema = :schema AND table_name = :table_name", [":schema" => DB_NAME, ":table_name" => $table_name])
            ->select("", ["table_comment AS comment"])
            ->execute()->fetchObject()->comment;
    }


    /**
     * @inheritdoc
     */
    public static function getColumnComment(string $table_name, string $column_name): string{
        $cache = Cache::getByBundleAndKey("column_comment", "{$table_name}-{$column_name}");
        if($cache){
            return strval($cache->value);
        }else{
            $comment_result = (new SelectQueryPreparer("INFORMATION_SCHEMA.COLUMNS", "", false))
            ->condition("TABLE_SCHEMA = :table_schema", [":table_schema" => DB_NAME])
            ->condition("TABLE_NAME = :table_name", [":table_name" => $table_name])
            ->condition("COLUMN_NAME = :column_name", [":column_name" => $column_name])
            ->select("", ["COLUMN_COMMENT"])
            ->execute()->fetchObject();
            if($comment_result){
                $comment = strval($comment_result->COLUMN_COMMENT);
            }else{
                $comment = "";
            }
            Cache::set("column_comment", "{$table_name}-{$column_name}", $comment);
            return $comment;
        }
    }
}

function get_field_from_object(&$object, $field)
{
    return isset($object->$field) ? $object->$field : "";
}
