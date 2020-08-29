<?php

namespace CoreDB\Kernel;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\TableDefinition;
use DirectoryIterator;
use Exception;
use Src\Entity\Cache;
use Symfony\Component\Yaml\Yaml;

class ConfigurationManager
{

    private static ?ConfigurationManager $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function importTableConfiguration()
    {
        $tables = [];
        foreach (new DirectoryIterator("../config/table_structure") as $file) {
            if ($file->isDot()) {
                continue;
            }
            $definition = Yaml::parseFile($file->getPathname());
            $table_definition = TableDefinition::getDefinition($definition["table_name"]);
            $table_definition->setComment($definition["table_comment"]);
            $table_definition->fields = [];
            $fields = $definition["fields"];
            $dataTypes = CoreDB::database()->dataTypes();
            foreach ($fields as $field) {
                /**
                 * @var DataTypeAbstract
                 */
                $dataType = new $dataTypes[$field["type"]]($field["column_name"]);
                $dataType->comment = $field["comment"];
                $dataType->isUnique = $field["isUnique"];
                if ($dataType instanceof ShortText) {
                    $dataType->length = $field["length"];
                } elseif ($dataType instanceof TableReference) {
                    $dataType->reference_table = $field["reference_table"];
                } elseif ($dataType instanceof EnumaratedList) {
                    $dataType->values = $field["values"];
                }
                $table_definition->addField($dataType);
                $tables[$table_definition->table_name] = $table_definition;
            }
        }
        $table_list = CoreDB::database()->getTableList();
        $new_table_list = array_keys($tables);
        $existingChangedTables = array_intersect($table_list, $new_table_list);
        $createdTables = array_diff($new_table_list, $table_list);
        $droppedTables = array_diff($table_list, $new_table_list);

        $db_query = "";
        $alterQueryPreparer = CoreDB::database()->alter();
        foreach ($existingChangedTables as $alter_table) {
            try {
                $alterQueryPreparer->addTableDefinition($tables[$alter_table]);
            } catch (Exception $ex) {
            }
        }
        $db_query .= implode("\n", $alterQueryPreparer->queries);
        foreach ($createdTables as $create_table) {
            $db_query .= "\n" . CoreDB::database()->create($tables[$create_table], true)->getQuery();
        }
        foreach ($droppedTables as $drop_table) {
            $db_query .= "\n" . CoreDB::database()->drop($drop_table)->getQuery();
        }
        if ($db_query) {
            CoreDB::database()->query($db_query);
        }

        $alterQueryPreparer = CoreDB::database()->alter();
        foreach ($tables as $table) {
            try {
                $alterQueryPreparer->addTableDefinition($table);
            } catch (Exception $ex) {
            }
        }
    }

    public function exportTableConfiguration()
    {
        CoreDB::cleanDirectory("../config/table_structure");
        foreach (CoreDB::database()->getTableList() as $table_name) {
            $definition = TableDefinition::getDefinition($table_name);
            file_put_contents("../config/table_structure/{$table_name}.yml", Yaml::dump($definition->toArray(), 4, 2));
        }
    }

    public function clearCache(){
        Cache::clear();
        \CoreDB::cleanDirectory("../cache", true);
    }
}
