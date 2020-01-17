<?php
include 'CoreDBQueryPreparer.php';
include 'SelectQueryPreparer.php';
include 'InsertQueryPreparer.php';
include 'UpdateQueryPreparer.php';
include 'DeleteQueryPreparer.php';
include 'CreateQueryPreparer.php';
include 'AlterQueryPreparer.php';
include 'DBObject.class.php';

class CoreDB {
    private static $instance;
    private $connection;
            
    private $transactionSet = FALSE;
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
     * @return \self
     */
    public static function getInstance(){
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
            if($this->transactionSet){
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
            if($this->transactionSet){
                $this->connection->rollBack();
            }
            throw $ex;
        }
    }
    
    public function rollback() {
        if($this->transactionSet){
            $this->connection->rollBack();
        }
    }
    
    public function beginTransaction(){
        $this->transactionSet = TRUE;
        $this->connection->beginTransaction();
    }

    public function commit(){
        if($this->transactionSet){
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
 * 
 * @param string $query
 * @return PDOStatement
 */
function db_query(string $query) : PDOStatement {
    return CoreDB::getInstance()->query($query);
}

function db_create(string $tableName) {
    return new CreateQueryPreparer($tableName);
}

function db_alter(string $tableName){
    return new AlterQueryPreparer($tableName);
}

function db_truncate(string $table_name) {
    return db_query("TRUNCATE TABLE `$table_name`;")->execute();
}

function get_field_from_object(&$object, $field) {
    return isset($object->$field) ? $object->$field : "";
}

$data_types = [
  "INT" => [
      "value" => "INT",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && $definition[1] != "MUL" && strpos($definition[1], "int") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
          $field = new InputField($desc[0]);
          $field->setType("number");
          $field->setValue(get_field_from_object($object, $desc[0]));
          $field->setLabel($desc[0]);
          return [$field, "col-lg-3 col-md-4 col-sm-6"];
      }
      ],
  "DOUBLE" => [
      "value" => "DOUBLE",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "double") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
          $field = new InputField($desc[0]);
          $field->setType("number");
          $field->setValue(get_field_from_object($object, $desc[0]));
          $field->addAttribute("step", "0.01");
          $field->setLabel($desc[0]);
          return [$field, "col-lg-3 col-md-4 col-sm-6"];
      }
      ],
  "VARCHAR" => [
      "value" => "VARCHAR",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "varchar") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
          $field = new InputField($desc[0]);
          $field->setValue(get_field_from_object($object, $desc[0]));
          $field->setLabel($desc[0]);
          return [$field, "col-lg-3 col-md-4 col-sm-6"];
      }
      ],
  "TEXT" => [
      "value" => "TEXT",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "text") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
          $field = new TextareaField($desc[0]);
          
          $field->setValue(get_field_from_object($object, $desc[0]));
          $field->setLabel($desc[0]);
          return [$field, "col-xs-12"];
      }
      ],
  "LONGTEXT" => [
      "value" => "LONGTEXT",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "longtext") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
          $field = new TextareaField($desc[0]);
          $field->addClass("summernote");
          $field->setLabel($desc[0]);
          $field->setValue(htmlspecialchars_decode(get_field_from_object($object, $desc[0])));
          return [$field, "col-xs-12"];
      }
      ],
  "DATE" => [
      "value" => "DATE",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "date") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
            $field = new InputField($desc[0]);
            $field->addClass("dateinput");
            $field->setLabel($desc[0]);
            $field->setValue($object ? get_field_from_object($object, $desc[0]) : Utils::get_current_date());
            return [$field, "col-lg-3 col-md-4 col-sm-6"];
      }
      ],
  "DATETIME" => [
      "value" => "DATETIME",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "datetime") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
            $field = new InputField($desc[0]);
            $field->addClass("datetimeinput");
            $field->setLabel($desc[0]);
            $field->setValue($object ? get_field_from_object($object, $desc[0]) : Utils::get_current_date());
            return [$field, "col-lg-3 col-md-4 col-sm-6"];
      }
      ],
  "TIME" => [
      "value" => "TIME",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "time") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
            $field = new InputField($desc[0]);
            $field->addClass("timeinput");
            $field->setLabel($desc[0]);
            $field->setValue($object ? get_field_from_object($object, $desc[0]) : Utils::get_current_date());
            return [$field, "col-lg-3 col-md-4 col-sm-6"];
      }
      ],
  "TINYTEXT" => [
      "value" => "FILE",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "tinytext") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc){
            $file_name = $object ? get_field_from_object($object, $desc[0]) : "";
            $field = new FileField($desc[0], $file_name); 
            $field->setLabel($desc[0]);
            $file_name ? $field->setFileURL(BASE_URL."/files/uploaded/{$object->table}/{$desc[0]}/{$file_name}") : NOEXPR;
            return [$field, "col-sm-12"];
      }
      ],
  "MUL" => [
      "value" => "REFERENCE",
      "selected_callback" => function ($definition){
        return [
            "checked" => $definition && strpos($definition[1], "MUL") === 0 ? "selected" : "",
        ];
      },
      "input_field_callback" => function($object, $desc, $table){
        $fk_description = get_foreign_key_description($table, $desc[0])->fetch(PDO::FETCH_NUM);
        $table_description = get_table_description($fk_description[0]);;
        $entries = db_select($fk_description[0])->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
        $options = [];
        foreach ($entries as $entry){
            $options[$entry[0]] = $entry[1];
        }
        $field = new SelectField($desc[0]);
        $field->setLabel($desc[0]);
        $field->setOptions($options)
                ->setValue(get_field_from_object($object, $desc[0]))
                ->addClass("autocomplete")
                ->addAttribute("data-reference-table", $fk_description[0])
                ->addAttribute("data-reference-column", $table_description[1][0]);
        return [$field, "col-lg-3 col-md-4 col-sm-6"];
      }
      ]
];

function get_supported_data_types() : array{
    global $data_types;
    return $data_types;
}

function object_map(&$object, array $array){
    foreach ($array as $key => $value){
            $object->$key = $value;
    }
}

function convert_object_to_array(&$object) : array{
    $object_as_array = (array) $object;
    unset($object_as_array["ID"]);
    unset($object_as_array["table"]);
    return $object_as_array;
}

define("ROLES", "ROLES");
define("USERS", "USERS");
define("USERS_ROLES", "USERS_ROLES");
define("RESET_PASSWORD_QUEUE", "RESET_PASSWORD_QUEUE");
define("TRANSLATIONS", "TRANSLATIONS");
define("EMAILS", "EMAILS");
define("BLOCKED_IPS", "BLOCKED_IPS");
define("WATCHDOG", "WATCHDOG");
define("LOGINS", "LOGINS");
$system_tables = [ROLES, USERS, RESET_PASSWORD_QUEUE, USERS_ROLES, TRANSLATIONS, BLOCKED_IPS, WATCHDOG, LOGINS];

function get_system_tables() : array{
    global $system_tables;
    return $system_tables;
}

/**
 * 
 * @return array
 */
function get_information_scheme() : array{
    $results = CoreDB::getInstance()->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
    $tables = [];
    foreach ($results as $result){
        array_push($tables, $result[0]);
    }
    return $tables;
}

/**
 * returns table description
 * @param string $table
 * @return \strıng
 */
function get_table_description(string $table, bool $mul_important = true) : array {
    $descriptions = db_query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_NUM);
    foreach ($descriptions as $index => $desc){
        if($mul_important && $desc[3] == "UNI" && is_unique_foreign_key($table, $desc[0])){
            $descriptions[$index][3] = "MUL-UNI";
        }
    }
    return $descriptions;
}
/**
 * 
 * @param string $table
 * @param string $foreignKey
 * @return PDOStatement
 */
function get_foreign_key_description(string $table, string $foreignKey) : PDOStatement {
    return db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
            ->select("", ["REFERENCED_TABLE_NAME","REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND TABLE_NAME = :table AND COLUMN_NAME = :column")
            ->params(["scheme" => DB_NAME, "table" => $table, ":column" => $foreignKey])->execute();
}
/**
 * Returns foreign keys referenced to table
 * @param string $table
 * @return array
 */
function get_references_to_table(string $table) : array {
    return db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
            ->select("", ["TABLE_NAME","COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND REFERENCED_TABLE_NAME = :table")
            ->params(["scheme" => DB_NAME, "table" => $table])->execute()->fetchAll(PDO::FETCH_NUM);
}
/**
 * Returns foreign keys on table
 * @param string $table
 * @return PDOStatement
 */
function get_table_references(string $table) : PDOStatement {
    return db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
            ->select("", ["REFERENCED_TABLE_NAME","REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND TABLE_NAME = :table")
            ->params(["scheme" => DB_NAME, "table" => $table])->execute();
}

/**
 * Returns all foreign key descriptions in database
 * @return PDOStatement All defined references in database
 */
function get_all_table_references() : PDOStatement {
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
function is_unique_foreign_key(string $table, string $uni) : bool {
    return get_foreign_key_description($table, $uni)->rowCount() !== 0;
}

function get_file_input(string $file_field, string $file_name = "") : string {
    return
    "<div >
        <div class='btn btn-success col-sm-2 col-xs-12 file-field'>
            "._t(83)."
        </div>
        <input type='file' name='$file_field' style='display: none;'/>
        <div class='col-sm-10 col-xs-12'>
            <input class='file-path form-control' type='text' value='$file_name' placeholder='"._t(113)."'/>
        </div>
    </div>";
}