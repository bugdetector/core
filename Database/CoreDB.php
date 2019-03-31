<?php
include 'CoreDBQueryPreparer.php';
include 'SelectQueryPreparer.php';
include 'InsertQueryPreparer.php';
include 'UpdateQueryPreparer.php';
include 'DeleteQueryPreparer.php';
include 'DBObject.class.php';

class CoreDB {
    private static $instance;
    private $connection;
            
    private $transactionSet = FALSE;
    private function __construct(){
        try{
            self::$instance = $this;
            $this->connection = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME, DB_USER, DB_PASSWORD
                    , array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
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
    public function execute(CoreDBQueryPreparer $query){
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
    public function query(string $query){
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
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
}

/**
 * 
 * @param string $tableName
 * @param string $alias
 * @return \SelectQueryPreparer
 */
function db_select(string $tableName, string $alias = "", bool $quote = TRUE){
    return new SelectQueryPreparer($tableName, $alias, $quote);
}

/**
 * 
 * @param string $tableName
 * @param array $fields
 * @return \InsertQueryPreparer
 */
function db_insert(string $tableName, array $fields){
    return new InsertQueryPreparer($tableName, $fields);
}
/**
 * 
 * @param string $tableName
 * @param array $fields
 * @return \UpdateQueryPreparer
 */
function db_update(string $tableName, array $fields){
    return new UpdateQueryPreparer($tableName, $fields);
}
/**
 * 
 * @param string $tableName
 * @param string $alias
 * @return \DeleteQueryPreparer
 */
function db_delete(string $tableName){
    return new DeleteQueryPreparer($tableName);
}

/**
 * 
 * @param string $query
 * @return PDOStatement
 */
function db_query(string $query) {
    return CoreDB::getInstance()->query($query);
}

function db_truncate(string $table_name) {
    return db_query("TRUNCATE TABLE `$table_name`;")->execute();
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
      echo "<input class='form-control' type='number' name='{$desc[0]}' value='".($object ? get_field_from_object($object, $desc[0]) : "")."'/>";
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
      echo "<input class='form-control' type='number' name='{$desc[0]}' value='".($object ? get_field_from_object($object, $desc[0]) : "")."'/>";
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
      echo "<input class='form-control' type='text' name='{$desc[0]}' value='".($object ? get_field_from_object($object, $desc[0]) : "")."'/>";
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
      echo "<textarea type='text' class='form-control' name='{$desc[0]}'>".($object ? get_field_from_object($object, $desc[0]) : "")."</textarea>";
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
          echo "<textarea class='summernote' name='{$desc[0]}'>".($object ? htmlspecialchars_decode(get_field_from_object($object, $desc[0])) : "")."</textarea>";
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
      echo "<input type='text' value='".($object ? get_field_from_object($object, $desc[0]) : get_current_date() )."' class='form-control datetimeinput' name='{$desc[0]}'/>";
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
      echo "<input type='text' value='".($object ? get_field_from_object($object, $desc[0]) : get_current_date() )."' class='form-control datetimeinput' name='{$desc[0]}'/>";
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
      echo "<input type='text' value='".($object ? get_field_from_object($object, $desc[0]) : get_current_date() )."' class='form-control datetimeinput' name='{$desc[0]}'/>";
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
        echo $file_name ? "<a class='file' href='".BASE_URL."/files/uploaded/{$object->table}/{$desc[0]}/{$file_name}' target='_blank'>$file_name</a>"
                . "<input value='$file_name' name='$desc[0]' style='display:none;'>" : "";
        echo_file_input($desc[0], $file_name);
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
        $description = get_foreign_key_description($table, $desc[0])->fetch(PDO::FETCH_NUM);
        $keys = db_select($description[0])->select("", [$description[1]])->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
        $entry = db_select($description[0])->orderBy("ID")->execute()->fetchAll(PDO::FETCH_NUM);
        $length = count($keys); ?>
        <select class="form-control selectpicker" data-live-search="true" name="<?php echo $desc[0];?>">
            <?php
            $selected_field = $object ? get_field_from_object($object, $desc[0]) : "";
            for($i = 0; $i< $length; $i++){
                ?> <option value="<?php echo $keys[$i][0];?>" <?php echo $selected_field == $keys[$i][0] ? "selected" : ""; ?>><?php echo $entry[$i][0]." ".$entry[$i][1];?></option>
          <?php
            }
          ?>
        </select>
        <?php
      }
      ]
];

function get_supported_data_types() {
    global $data_types;
    return $data_types;
}

function object_map(&$object, array $array){
    foreach ($array as $key => $value){
            $object->$key = $value;
    }
}

function convert_object_to_array(&$object){
    $object_as_array = (array) $object;
    unset($object_as_array["ID"]);
    unset($object_as_array["table"]);
    return $object_as_array;
}

define("LOGINS", "LOGINS");
define("ROLES", "ROLES");
define("USERS", "USERS");
define("USERS_ROLES", "USERS_ROLES");
define("RESET_PASSWORD_QUEUE", "RESET_PASSWORD_QUEUE");
define("TRANSLATIONS", "TRANSLATIONS");
define("EMAILS", "EMAILS");
define("BLOCKED_IPS", "BLOCKED_IPS");
define("WATCHDOG", "WATCHDOG");
$system_tables = [LOGINS, ROLES, USERS, RESET_PASSWORD_QUEUE, USERS_ROLES, TRANSLATIONS, BLOCKED_IPS, WATCHDOG];

function get_system_tables(){
    global $system_tables;
    return $system_tables;
}

/**
 * 
 * @return array
 */
function get_information_scheme(){
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
function get_table_description(string $table) {
    $descriptions = db_query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_NUM);
    foreach ($descriptions as $index => $desc){
        if($desc[3] == "UNI" && is_unique_foreign_key($table, $desc[0])){
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
function get_foreign_key_description(string $table, string $foreignKey) {
    return db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
            ->select("", ["REFERENCED_TABLE_NAME","REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND TABLE_NAME = :table AND COLUMN_NAME = :column")
            ->params(["scheme" => DB_NAME, "table" => $table, "column" => $foreignKey])->execute();
}
/**
 * Returns foreign keys referenced to table
 * @param string $table
 * @return array
 */
function get_references_to_table(string $table) {
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
function get_table_references(string $table) {
    return db_select("INFORMATION_SCHEMA.KEY_COLUMN_USAGE", "", FALSE)
            ->select("", ["REFERENCED_TABLE_NAME","REFERENCED_COLUMN_NAME"])
            ->condition("REFERENCED_TABLE_SCHEMA = :scheme AND TABLE_NAME = :table")
            ->params(["scheme" => DB_NAME, "table" => $table])->execute();
}

/**
 * Returns all foreign key descriptions in database
 * @return PDOStatement All defined references in database
 */
function get_all_table_references() {
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
function is_unique_foreign_key(string $table, string $uni) {
    return get_foreign_key_description($table, $uni)->rowCount() !== 0;
}