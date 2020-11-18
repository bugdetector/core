<?php

namespace CoreDB\Kernel;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use Exception;
use PDO;
use PDOException;
use Src\Entity\DBObject;
use Src\Entity\File;
use Src\Entity\Translation;
use Src\Form\InsertForm;
use Src\Form\Widget\FormWidget;
use Src\Theme\ResultsViewer;
use Src\Theme\View;
use Src\Views\Table;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

abstract class TableMapper implements SearchableInterface
{
    public Integer $ID;
    public DateTime $created_at;
    public DateTime $last_updated;
    public string $entityName = "";
    public ?array $entityConfig = [];

    protected $changed_fields;

    public function __construct(string $tableName = null, array $mapData = [])
    {
        $table_definition = TableDefinition::getDefinition($this->getTableName());
        /**
         * @var DataTypeAbstract $field
         */
        foreach ($table_definition->fields as $field_name => $field) {
            $this->{$field_name} = $field;
        }
        $this->map($mapData, true);
        $this->changed_fields = [];

        $entityConfig = CoreDB::config()->getEntityInfoByClass(static::class);
        if ($entityConfig) {
            $this->entityName = array_key_first($entityConfig);
            $this->entityConfig =  $entityConfig[$this->entityName];
            if (isset($this->entityConfig[EntityReference::CONNECTION_MANY_TO_MANY])) {
                foreach ($this->entityConfig[EntityReference::CONNECTION_MANY_TO_MANY] as $fieldEntityName => $config) {
                    $this->{$fieldEntityName} = new EntityReference(
                        $fieldEntityName,
                        $this,
                        $config,
                        EntityReference::CONNECTION_MANY_TO_MANY
                    );
                }
            }
            if (isset($this->entityConfig[EntityReference::CONNECTION_ONE_TO_MANY])) {
                foreach ($this->entityConfig[EntityReference::CONNECTION_ONE_TO_MANY] as $fieldEntityName => $config) {
                    $this->{$fieldEntityName} = new EntityReference(
                        $fieldEntityName,
                        $this,
                        $config,
                        EntityReference::CONNECTION_ONE_TO_MANY
                    );
                }
            }
            if (isset($this->entityConfig[EntityReference::CONNECTION_ONE_TO_ONE])) {
                foreach ($this->entityConfig[EntityReference::CONNECTION_ONE_TO_ONE] as $fieldEntityName => $config) {
                    $this->{$fieldEntityName} = new EntityReference(
                        $fieldEntityName,
                        $this,
                        $config,
                        EntityReference::CONNECTION_ONE_TO_ONE
                    );
                }
            }
        }
    }
    public function __get($name)
    {
        return isset($this->{$name}) ? $this->{$name} : null;
    }

    /**
     * Return associated table name.
     * @return string
     *  Table name.
     */
    abstract public static function getTableName(): string;

    /**
     * Get an instance of object with given filter
     * @return TableMapper
     *  Object.
     */
    public static function get($filter)
    {
        if (!is_array($filter)) {
            $filter = [
                "ID" => $filter
            ];
        }
        return static::find($filter, static::getTableName());
    }

    /**
     * Get all objects matches given filter.
     * @return array
     *  TableMapper objects.
     */
    public static function getAll(array $filter): array
    {
        return static::findAll($filter, static::getTableName());
    }


    /**
     * Copy of ::get. Needs table name.
     * @return TableMapper
     *  Object.
     */
    public static function find(array $filter, string $table, $orderBy = "ID"): ?TableMapper
    {
        $query = \CoreDB::database()->select($table);
        foreach ($filter as $key => $value) {
            $query->condition($key, $value);
        }
        $result = $query->orderBy($orderBy)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC) ?: null;
        if ($result) {
            $className = get_called_class();
            /**
             * @var TableMapper
             */
            $object = new $className($table, $result);
        } else {
            $object = null;
        }
        return $object;
    }

    /**
     * Copy of ::getAll. Need table name.
     * @return array
     *  TableMapper objects.
     */
    public static function findAll(array $filter, string $table, $orderBy = "ID"): array
    {
        $query = CoreDB::database()->select($table);
        foreach ($filter as $key => $value) {
            $query->condition($key, $value);
        }
        $results = $query->orderBy($orderBy)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
        $objects = [];
        if ($results) {
            $className = get_called_class();
            foreach ($results as $result) {
                /**
                 * @var TableMapper
                 */
                $object = new $className($table, $result);
                $objects[] = $object;
            }
        }
        return $objects;
    }

    /**
     * Set fields of object using an array with same keys
     * @param array $array
     *  Containing field values to set
     */
    public function map(array $array, bool $isConstructor = false)
    {
        $this->changed_fields = [];
        foreach ($array as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }
            if (!$isConstructor && $this->{$key}->getValue() != $value) {
                $this->changed_fields[$key] = [
                    "old_value" => $this->{$key}->getValue(),
                    "new_value" => $value
                ];
            }
            $this->$key->setValue($value);
        }
    }

    /**
     * Converts an object to array including private fields
     * @return \array
     */
    public function toArray(): array
    {
        foreach ($this as $field_name => $field) {
            if (
                !($field instanceof DataTypeAbstract) ||
                ($field instanceof EntityReference) ||
                in_array($field_name, ["ID", "created_at", "last_updated", "changed_fields"])
            ) {
                continue;
            }
            $object_as_array[$field_name] = $field->getValue();
        }
        return $object_as_array;
    }


    protected function insert()
    {
        $statement = CoreDB::database()->insert($this->getTableName(), $this->toArray())->execute();
        $this->ID->setValue(\CoreDB::database()->lastInsertId());
        return $statement;
    }

    protected function update()
    {
        return CoreDB::database()
            ->update($this->getTableName(), $this->toArray())
            ->condition("ID", $this->ID->getValue())
            ->execute();
    }

    public function save()
    {
        if (!$this->ID->getValue()) {
            $this->insert();
        }
        foreach ($this as $field_name => $field) {
            if ($field instanceof EntityReference) {
                /** @var EntityReference */
                $field->object = &$this;
                $field->save();
            }
        }
        return $this->update();
    }

    public function delete(): bool
    {
        if (!$this->ID->getValue()) {
            return false;
        }
        /**
         * @var DataTypeAbstract $field
         */
        foreach ($this as $field_name => $field) {
            if ($field instanceof \CoreDB\Kernel\Database\DataType\File) {
                /** @var File $file */
                if ($file = File::get($field->getValue())) {
                    $file->unlinkFile();
                }
            }
        }
        return boolval(
            CoreDB::database()->delete($this->getTableName())->condition("ID", $this->ID)->execute()
        );
    }

    /**
     * Truncate associated table.
     * @throws PDOException
     */
    public static function clear()
    {
        return static::clearTable(static::getTableName());
    }
    protected static function clearTable($table)
    {
        return CoreDB::database()->truncate($table)->execute();
    }


    public function getFileUrlForField($field_name)
    {
        return BASE_URL . "/files/uploaded/" . $this->getTableName() . "/$field_name/" . $this->$field_name;
    }

    public function getForm()
    {
        return new InsertForm($this);
    }

    public function getFormFields($name, bool $translateLabel = true): array
    {
        $fields = [];
        foreach ($this as $field_name => $field) {
            if (
                !($field instanceof DataTypeAbstract) ||
                in_array($field_name, ["ID", "table", "created_at", "last_updated"])
            ) {
                continue;
            }
            $widget = $this->getFieldWidget($field_name, $translateLabel);
            if (!$widget) {
                continue;
            }
            $inputName = $name . "[{$field_name}]";
            if ($field instanceof EntityReference) {
                $inputName .= "[]";
            }
            if ($widget instanceof FormWidget) {
                /**
                 * @var FormWidget $widget
                 */
                $widget->setName($inputName);
            }
            $fields[$field_name] = $widget;
        }
        return $fields;
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        return $this->$field_name->getWidget()
            ->setLabel($translateLabel ? Translation::getTranslation($field_name) : $field_name);
    }

    /**
     * @inheritdoc
     */
    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $fields = [];
        foreach ($this as $field_name => $field) {
            if (!($field instanceof DataTypeAbstract)) {
                continue;
            }
            $inputName = $field_name;
            if ($field instanceof EntityReference) {
                $inputName .= "[]";
            }
            $widget = $field->getSearchWidget();
            if ($widget) {
                $fields[$field_name] = $widget->setName($inputName)
                    ->setLabel($translateLabel ? Translation::getTranslation($field_name) : $field_name);
            }
        }
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = [""];
        /**
         * @var DataTypeAbstract $field
         */
        foreach (TableDefinition::getDefinition(static::getTableName())->fields as $field) {
            $headers[$field->column_name] = $translateLabel ?
                Translation::getTranslation($field->column_name) : $field->column_name;
        }
        return $headers;
    }

    /**
     * @inheritdoc
     */
    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()->select(static::getTableName())
            ->select(static::getTableName(), ["ID AS edit_actions", "*"]);
    }


    /**
     * @inheritdoc
     */
    public function getPaginationLimit(): int
    {
        return self::PAGE_LIMIT;
    }

    /**
     * @inheritdoc
     */
    public function getResultsViewer(): ResultsViewer
    {
        return new Table();
    }

    /**
     * @inheritdoc
     */
    public function postProcessRow(&$row): void
    {
        if (!isset($row["edit_actions"])) {
            return;
        }
        $row["edit_actions"] = ViewGroup::create("div", "d-flex")
            ->addField(
                ViewGroup::create("a", "mr-2 rowdelete")
                    ->addField(
                        ViewGroup::create("i", "fa fa-times text-danger core-control")
                    )->addField(
                        TextElement::create(
                            Translation::getTranslation("delete")
                        )->addClass("sr-only")
                    )
                    ->addAttribute("data-table", $this->getTableName())
                    ->addAttribute("data-id", $row["edit_actions"])
                    ->addAttribute("href", "#")
            )->addField(
                ViewGroup::create("a", "ml-2")
                    ->addField(
                        ViewGroup::create("i", "fa fa-edit text-primary core-control")
                    )->addField(
                        TextElement::create(
                            Translation::getTranslation("edit")
                        )->addClass("sr-only")
                    )
                    ->addAttribute("href", $this->editUrl($row["edit_actions"]))
            );
    }

    /**
     * Return entity action buttons.
     * @return array
     *  String convertibable objects.
     */
    public function actions(): array
    {
        return [
            ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-1 mb-1")
                ->addField(
                    ViewGroup::create("i", "fa fa-plus text-white-50")
                )->addAttribute("href", BASE_URL . "/admin/entity/insert/{$this->entityName}")
                ->addField(TextElement::create(Translation::getTranslation("add")))
        ];
    }
    public function editUrl($value = null)
    {
        if (!$value) {
            $value = $this->ID->getValue();
        }
        $isEntity = static::class != DBObject::class;
        return BASE_URL . "/admin/" .
        ($isEntity ? "entity" : "table") . "/insert/" .
        ($isEntity ? $this->entityName : $this->getTableName()) . "/{$value}";
    }

    public function includeFiles($from = null)
    {
        foreach (\CoreDB::normalizeFiles($from) as $file_key => $fileInfo) {
            if ($fileInfo["size"] != 0) {
                if ($this->$file_key->getValue()) {
                    /** @var File  */
                    $file = File::get($this->$file_key);
                    $file->unlinkFile();
                } else {
                    $file = new File();
                }
                if ($file->storeUploadedFile($this->getTableName(), $file_key, $fileInfo)) {
                    $this->{$file_key}->setValue($file->ID);
                    $this->save();
                } else {
                    \CoreDB::database()->rollback();
                    throw new Exception(Translation::getTranslation("an_error_occured"));
                }
            }
        }
    }
}
