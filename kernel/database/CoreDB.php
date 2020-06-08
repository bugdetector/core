<?php
include 'CoreDBQueryPreparer.php';
include 'SelectQueryPreparer.php';
include 'InsertQueryPreparer.php';
include 'UpdateQueryPreparer.php';
include 'DeleteQueryPreparer.php';
include 'CreateQueryPreparer.php';
include 'AlterQueryPreparer.php';
include 'DropQueryPreparer.php';
include 'DBObject.class.php';
/**
 * @property \PDO $connection
 */
class CoreDB {
    private static $instance;
    private $connection;

    private function __construct(){
        try{
            self::$instance = $this;
            $this->connection = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->query("SET NAMES UTF8");
        } catch (PDOException $ex){
            die("Can't connect to database.");
        }
    }
    /**
     * 
     * @return \CoreDB
     */
    public static function getInstance() : CoreDB{
        if(self::$instance == NULL){
            return new self();
        }
        return self::$instance;
    }
    
    /**
     * 
     * @param CoreDBQueryPreparer $query
     * @return PDOStatement
     */
    public function execute(CoreDBQueryPreparer $query) : PDOStatement{
        try {
            $statement = $this->connection->prepare($query->getQuery());
            $statement->execute($query->getParams());
            return $statement;    
        } catch (PDOException $ex) {
            if($this->connection->inTransaction()){
                $this->connection->rollBack();
            }
            throw $ex;
        }
    }
    
    /**
     * 
     * @param string $query
     * @return PDOStatement
     */
    public function query(string $query) : PDOStatement{
        try{
            return $this->connection->query($query);
        } catch (PDOException $ex){
            if($this->connection->inTransaction()){
                $this->connection->rollBack();
            }
            throw $ex;
        }
    }
    
    public function rollback() {
        if($this->connection->inTransaction()){
            $this->connection->rollBack();
        }
    }
    
    public function beginTransaction(){
        $this->connection->beginTransaction();
    }

    public function commit(){
        if($this->connection->inTransaction()){
            $this->connection->commit();
        }
    }
    
    /**
     * 
     * @return int
     */
    public function lastInsertId() : int{
        return $this->connection->lastInsertId();
    }
    
    public static function quote(string $string){
        return CoreDB::getInstance()->_quote($string);
    }

    private function _quote(string $string){
        return $this->connection->quote($string);
    }

    public static function get_supported_data_types() : array{
        return [
            "INT" => [
                "value" => "INT",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && $definition["Type"] != "MUL" && strpos($definition["Type"], "int") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                    $field = new InputField($desc["Field"]);
                    $field->setType("number");
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    $field->setLabel($desc["Field"]);
                    return [$field, "col-lg-3 col-md-4 col-sm-6"];
                }
                ],
            "DOUBLE" => [
                "value" => "DOUBLE",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "double") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                    $field = new InputField($desc["Field"]);
                    $field->setType("number");
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    $field->addAttribute("step", "0.01");
                    $field->setLabel($desc["Field"]);
                    return [$field, "col-lg-3 col-md-4 col-sm-6"];
                }
                ],
            "VARCHAR" => [
                "value" => "VARCHAR",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "varchar") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                    $field = new InputField($desc["Field"]);
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    $field->setLabel($desc["Field"]);
                    return [$field, "col-lg-3 col-md-4 col-sm-6"];
                }
                ],
            "TEXT" => [
                "value" => "TEXT",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "text") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                    $field = new TextareaField($desc["Field"]);
                    
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    $field->setLabel($desc["Field"]);
                    return [$field, "col-sm-12"];
                }
                ],
            "LONGTEXT" => [
                "value" => "LONGTEXT",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "longtext") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                    $field = new TextareaField($desc["Field"]);
                    $field->addClass("summernote");
                    $field->setLabel($desc["Field"]);
                    $field->setValue(get_field_from_object($object, $desc["Field"]));
                    return [$field, "col-sm-12"];
                }
                ],
            "DATE" => [
                "value" => "DATE",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "date") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                      $field = new InputField($desc["Field"]);
                      $field->addClass("dateinput datetimepicker-input");
                      $field->addAttribute("id", $desc["Field"]);
                      $field->addAttribute("data-target", "#".$desc["Field"]);
                      $field->addAttribute("data-toggle", "datetimepicker");
                      $field->addAttribute("autocomplete", "off");
                      $field->setLabel($desc["Field"]);
                      $field->setValue($object ? get_field_from_object($object, $desc["Field"]) : Utils::get_current_date());
                      return [$field, "col-lg-3 col-md-4 col-sm-6"];
                }
                ],
            "DATETIME" => [
                "value" => "DATETIME",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "datetime") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                      $field = new InputField($desc["Field"]);
                      $field->addClass("datetimeinput datetimepicker-input");
                      $field->addAttribute("id", $desc["Field"]);
                      $field->addAttribute("data-target", "#".$desc["Field"]);
                      $field->addAttribute("data-toggle", "datetimepicker");
                      $field->addAttribute("autocomplete", "off");
                      $field->setLabel($desc["Field"]);
                      $field->setValue($object ? get_field_from_object($object, $desc["Field"]) : Utils::get_current_date());
                      return [$field, "col-lg-3 col-md-4 col-sm-6"];
                }
                ],
            "TIME" => [
                "value" => "TIME",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "time") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                      $field = new InputField($desc["Field"]);
                      $field->addClass("timeinput datetimepicker-input");
                      $field->addAttribute("id", $desc["Field"]);
                      $field->addAttribute("data-target", "#".$desc["Field"]);
                      $field->addAttribute("data-toggle", "datetimepicker");
                      $field->addAttribute("autocomplete", "off");
                      $field->setLabel($desc["Field"]);
                      $field->setValue($object ? get_field_from_object($object, $desc["Field"]) : Utils::get_current_date());
                      return [$field, "col-lg-3 col-md-4 col-sm-6"];
                }
                ],
            "TINYTEXT" => [
                "value" => "FILE",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "tinytext") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc){
                      $file_name = $object ? get_field_from_object($object, $desc["Field"]) : "";
                      $field = new FileField($desc["Field"], $file_name); 
                      $field->setLabel($desc["Field"]);
                      $file_name ? $field->setFileURL(BASE_URL."/files/uploaded/{$object->table}/{$desc["Field"]}/{$file_name}") : "";
                      return [$field, "col-sm-12"];
                }
                ],
            "MUL" => [
                "value" => "REFERENCE",
                "selected_callback" => function ($definition){
                  return [
                      "checked" => $definition && strpos($definition["Type"], "MUL") === 0 ? "selected" : "",
                  ];
                },
                "input_field_callback" => function($object, $desc, $table){
                  $fk_description = CoreDB::get_foreign_key_description($table, $desc["Field"]);
                  $table_description = CoreDB::get_table_description($fk_description["REFERENCED_TABLE_NAME"]);;
                  $entries = db_select($fk_description[0])->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
                  $options = [];
                  foreach ($entries as $entry){
                      $options[$entry[0]] = $entry[1];
                  }
                  $field = new SelectField($desc["Field"]);
                  $field->setLabel($desc["Field"]);
                  $field->setOptions($options)
                          ->setValue(get_field_from_object($object, $desc["Field"]))
                          ->addClass("autocomplete")
                          ->addAttribute("data-reference-table", $fk_description["REFERENCED_TABLE_NAME"])
                          ->addAttribute("data-reference-column", $table_description[1]["Field"]);
                  return [$field, "col-lg-3 col-md-4 col-sm-6"];
                }
                ]
            ];
    }

    /**
     * returns table description
     * @param string $table
     * @return \strıng
     */
    public static function get_table_description(string $table, bool $mul_important = true) : array {
        $cache = Cache::getByBundleAndKey("table_description", $table);
        if($cache){
            return json_decode($cache->value, TRUE);
        }else{
            $descriptions = db_query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($descriptions as $index => $desc){
                if($mul_important && $desc["Key"] == "UNI" && self::is_unique_foreign_key($table, $desc["Field"])){
                    $descriptions[$index]["Key"] = "MUL-UNI";
                }
            }
            Cache::set("table_description", $table, json_encode($descriptions));
            return $descriptions;
        }
    }

    /**
     * Returns foreign keys referenced to table
     * @param string $table
     * @return array
     */
    public static function get_references_to_table(string $table) : array {
        return db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
                ->select("", ["TABLE_NAME","COLUMN_NAME"])
                ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND REFERENCED_TABLE_NAME = :table")
                ->params(["scheme" => DB_NAME, "table" => $table])->execute()->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Returns all foreign key descriptions in database
     * @return PDOStatement All defined references in database
     */
    public static function get_all_table_references() : PDOStatement {
        return db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
                ->select("", ["TABLE_NAME","COLUMN_NAME","REFERENCED_TABLE_NAME","REFERENCED_COLUMN_NAME"])
                ->condition("REFERENCED_TABLE_SCHEMA = :scheme")
                ->params(["scheme" => DB_NAME])->execute();
    }

    /**
     * Returns true if unique column is also a foreign key else returns false
     * @param string $table
     * @param string $uni
     * @return bool
     */
    public static function is_unique_foreign_key(string $table, string $uni) : bool {
        return count(CoreDB::get_foreign_key_description($table, $uni)) !== 0;
    }

    /**
     * Returns foreign keys on table
     * @param string $table
     * @return PDOStatement
     */
    public static function get_table_references(string $table) : PDOStatement {
        return db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
                ->select("", ["REFERENCED_TABLE_NAME","REFERENCED_COLUMN_NAME"])
                ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND TABLE_NAME = :table")
                ->params(["scheme" => DB_NAME, "table" => $table])->execute();
    }

    /**
     * 
     * @param string $table
     * @param string $foreignKey
     * @return PDOStatement
     */
    public static function get_foreign_key_description(string $table, string $foreignKey) : array {
        $cache = Cache::getByBundleAndKey("foreign_key_description", $table.$foreignKey);
        if($cache){
            return json_decode($cache->value, TRUE) ? : [];
        }else{
            $result = db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
            ->select("", ["REFERENCED_TABLE_NAME","REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND TABLE_NAME = :table AND COLUMN_NAME = :column")
            ->params(["scheme" => DB_NAME, "table" => $table, ":column" => $foreignKey])->execute()->fetch(PDO::FETCH_BOTH);
            if($result){
            Cache::set("foreign_key_description", $table.$foreignKey, json_encode($result));
            }
            return $result ? : [];
        }
    }
    
    /**
    * 
    * @return array
    */
    public static function get_information_scheme() : array{
       $results = CoreDB::getInstance()->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
       $tables = [];
       foreach ($results as $result){
           array_push($tables, $result[0]);
       }
       return $tables;
   }

   /**
    * @return string
    */
    public static function getTableComment($table_name) : string{
        return db_select("INFORMATION_SCHEMA.TABLES", "", false)
        ->condition("table_schema = :schema AND table_name = :table_name", [":schema" => DB_NAME, ":table_name" => $table_name])
        ->select("", ["table_comment AS comment"])
        ->execute()->fetchObject()->comment;
    }
}

/**
 * 
 * @param string $tableName
 * @param string $alias
 * @return \SelectQueryPreparer
 */
function db_select(string $tableName, string $alias = "", bool $quote = TRUE) : SelectQueryPreparer{
    return new SelectQueryPreparer($tableName, $alias, $quote);
}

/**
 * 
 * @param string $tableName
 * @param array $fields
 * @return \InsertQueryPreparer
 */
function db_insert(string $tableName, array $fields) : InsertQueryPreparer{
    return new InsertQueryPreparer($tableName, $fields);
}
/**
 * 
 * @param string $tableName
 * @param array $fields
 * @return \UpdateQueryPreparer
 */
function db_update(string $tableName, array $fields) : UpdateQueryPreparer{
    return new UpdateQueryPreparer($tableName, $fields);
}
/**
 * 
 * @param string $tableName
 * @param string $alias
 * @return \DeleteQueryPreparer
 */
function db_delete(string $tableName) : DeleteQueryPreparer{
    return new DeleteQueryPreparer($tableName);
}

/**
 * @param string $tableName
 * @param string $column
 * @return \DropQueryPreparer
 */
function db_drop(string $tableName, string $column = NULL) : DropQueryPreparer{
    return new DropQueryPreparer($tableName, $column);
}
/**
 * 
 * @param string $query
 * @return PDOStatement
 */
function db_query(string $query) : PDOStatement {
    return CoreDB::getInstance()->query($query);
}

function db_create(string $tableName) : CreateQueryPreparer {
    return new CreateQueryPreparer($tableName);
}

function db_alter(string $tableName) : AlterQueryPreparer{
    return new AlterQueryPreparer($tableName);
}

function db_truncate(string $table_name) {
    return db_query("TRUNCATE TABLE `$table_name`;")->execute();
}

function get_field_from_object(&$object, $field) {
    return isset($object->$field) ? $object->$field : "";
}