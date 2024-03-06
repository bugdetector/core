<?php

namespace CoreDB\Kernel\Database;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\UnsignedBigInteger;
use Src\Entity\Cache;

class TableDefinition
{
    public string $table_name = "";
    public string $table_comment = "";
    public array $fields = [];
    public bool $table_exist = false;

    public function __construct(string $table_name)
    {
        $this->table_name = $table_name;
    }

    public static function getDefinition(string $table_name, $doNotUseCache = false): ?TableDefinition
    {
        $cache = !$doNotUseCache ? Cache::getByBundleAndKey("table_definition", $table_name) : null;
        if ($cache) {
            $definition = unserialize(base64_decode($cache->value->getValue()));
        } else {
            $definition = new TableDefinition($table_name);
            if (in_array($table_name, CoreDB::database()->getTableList())) {
                $definition->fields = CoreDB::database()->getTableDescription($table_name);
                $definition->table_comment = CoreDB::database()->getTableComment($table_name);
                $definition->table_exist = true;
            } else {
                $definition->table_exist = false;
            }
            if (!$doNotUseCache) {
                $cache = Cache::set("table_definition", $table_name, base64_encode(serialize($definition)));
            }
        }
        /**
         * Migration:
         * This block detects system need to update ID field data type.
         * Will be removed in future relase.
         */
        if (
            @$definition->fields["ID"] &&
            !($definition->fields["ID"] instanceof UnsignedBigInteger) &&
            !$doNotUseCache
        ) {
            self::migrateIdFiedlsToUnsignedBigInteger();
            return TableDefinition::getDefinition($table_name, $doNotUseCache);
        }
        return $definition;
    }

    public function setComment(string $table_comment)
    {
        $this->table_comment = $table_comment;
    }

    public function addField(DataTypeAbstract $data_type)
    {
        if (in_array($data_type->column_name, ["ID", "created_at", "last_updated"])) {
            return;
        }
        if (!isset($this->fields["ID"])) {
            $id_field = (new UnsignedBigInteger("ID"));
            $id_field->isNull = false;
            $id_field->primary_key = true;
            $id_field->autoIncrement = true;
            $this->fields["ID"] = $id_field;
        }
        $this->fields[$data_type->column_name] = $data_type;
    }

    public function saveDefinition(AlterQueryPreparerAbstract &$query = null)
    {
        unset($this->fields["created_at"], $this->fields["last_updated"]);
        $created_at = new DateTime("created_at");
        $created_at->default = CoreDB::database()->currentTimestamp();
        $this->fields["created_at"] = $created_at;
        $last_updated = new DateTime("last_updated");
        $last_updated->default = CoreDB::database()->currentTimestampOnUpdate();
        $this->fields["last_updated"] = $last_updated;
        if ($this->table_exist) {
            if ($query) {
                $query->addTableDefinition($this);
            } else {
                CoreDB::database()->alter($this)->execute();
            }
        } else {
            CoreDB::database()->create($this)->execute();
        }
    }

    public function toArray(): array
    {
        $array = [];
        $array["table_name"] = $this->table_name;
        $array["table_comment"] = $this->table_comment;
        $array["fields"] = [];
        /**
         * @var DataTypeAbstract $field
         */
        foreach ($this->fields as $field_name => $field) {
            if (in_array($field_name, ["ID", "created_at", "last_updated"])) {
                continue;
            }
            $array["fields"][$field_name] = $field->toArray();
        }
        return $array;
    }

    /**
     * Migration:
     * This block migrated ID field data type to unsigned big integer.
     * Will be removed in future relase.
     */
    public static function migrateIdFiedlsToUnsignedBigInteger()
    {
        $allReferences = \CoreDB::database()->getAllTableReferences()->fetchAll(\PDO::FETCH_ASSOC);
        $dbQuery = "";
        $finishingDbQuery = "";
        foreach ($allReferences as $reference) {
            $fkDescriptions = \CoreDB::database()
                ->getForeignKeyDescription($reference["TABLE_NAME"], $reference["COLUMN_NAME"]);
            foreach ($fkDescriptions as $fkDescription) {
                $dbQuery .= "ALTER TABLE `{$reference["TABLE_NAME"]}` DROP FOREIGN KEY " .
                    "`{$fkDescription["CONSTRAINT_NAME"]}`;";
                $dbQuery .= "ALTER TABLE `{$reference["TABLE_NAME"]}` CHANGE `{$reference["COLUMN_NAME"]}`" .
                    " `{$reference["COLUMN_NAME"]}` BIGINT UNSIGNED;";
                $finishingDbQuery .= "ALTER TABLE `{$reference["TABLE_NAME"]}` " .
                    "ADD FOREIGN KEY (`{$reference["COLUMN_NAME"]}`) " .
                    "REFERENCES `{$fkDescription["REFERENCED_TABLE_NAME"]}`(ID);";
            }
        }
        foreach (\CoreDB::database()->getTableList() as $table_name) {
            $dbQuery .= "ALTER TABLE `{$table_name}` CHANGE `ID` `ID` BIGINT UNSIGNED AUTO_INCREMENT;";
        }
        $dbQuery .= $finishingDbQuery;
        if ($dbQuery) {
            \CoreDB::database()->query("SET FOREIGN_KEY_CHECKS = 0;");
            \CoreDB::database()->query($dbQuery);
        }
        Cache::clear();
        \CoreDB::config()->importTableConfiguration();
    }
}
