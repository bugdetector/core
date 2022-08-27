<?php

namespace CoreDB\Kernel;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\File as DataTypeFile;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use PDO;
use PDOException;
use Src\Controller\Admin\Entity\InsertController;
use Src\Controller\Admin\Table\InsertController as TableInsertController;
use Src\Entity\DynamicModel;
use Src\Entity\File;
use Src\Entity\Translation;
use Src\Form\InsertForm;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\JWT;
use Src\Theme\ResultsViewer;
use Src\Theme\View;
use Src\Views\Link;
use Src\Views\Table;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

abstract class Model implements SearchableInterface
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
            foreach ($this->entityConfig as $connection => $configData) {
                if (
                    in_array($connection, [
                    EntityReference::CONNECTION_MANY_TO_MANY,
                    EntityReference::CONNECTION_MANY_TO_ONE,
                    EntityReference::CONNECTION_ONE_TO_MANY,
                    EntityReference::CONNECTION_ONE_TO_ONE
                    ])
                ) {
                    foreach ($configData as $fieldEntityName => $config) {
                        $this->{$fieldEntityName} = new EntityReference(
                            $fieldEntityName,
                            $this,
                            $config,
                            $connection
                        );
                    }
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
     * @return Model
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
     *  Model objects.
     */
    public static function getAll(array $filter): array
    {
        return static::findAll($filter, static::getTableName());
    }


    /**
     * Copy of ::get. Needs table name.
     * @return Model
     *  Object.
     */
    public static function find(array $filter, string $table, $orderBy = "ID"): ?Model
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
             * @var Model
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
     *  Model objects.
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
                 * @var Model
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
        $result = CoreDB::database()
            ->update($this->getTableName(), $this->toArray())
            ->condition("ID", $this->ID->getValue())
            ->execute();
        if ($result) {
            foreach ($this as $fieldName => $field) {
                if ($field instanceof DataTypeFile && @$this->changed_fields[$fieldName]) {
                    $file = File::get($this->changed_fields[$fieldName]["old_value"]);
                    if ($file) {
                        $file->delete();
                    }
                }
            }
        }
        return $result;
    }

    public function save()
    {
        if (!$this->ID->getValue()) {
            $this->insert();
        }
        foreach ($this as $field_name => $field) {
            if (
                ($field instanceof CoreDB\Kernel\Database\DataType\File) &&
                $field->getValue()
            ) {
                \CoreDB::database()->update(File::getTableName(), [
                    "status" => File::STATUS_PERMANENT
                ])
                ->condition("ID", $field->getValue())
                ->execute();
            } elseif ($field instanceof EntityReference) {
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
         * @var File[] $filesWillDelete
         */
        $filesWillDelete = [];
        /**
         * @var DataTypeAbstract $field
         */
        foreach ($this as $field_name => $field) {
            if ($field instanceof \CoreDB\Kernel\Database\DataType\File) {
                /** @var File $file */
                if ($file = File::get($field->getValue())) {
                    $filesWillDelete[] = $file;
                }
            } elseif ($field instanceof EntityReference) {
                $this->$field_name->setValue([]);
            }
        }
        $this->save();
        $deleted = boolval(
            CoreDB::database()->delete($this->getTableName())->condition("ID", $this->ID)->execute()
        );
        if ($deleted) {
            foreach ($filesWillDelete as $file) {
                $file->delete();
            }
            return true;
        } else {
            return false;
        }
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
        /** @var File */
        $file = File::get($this->$field_name);
        return $file->getUrl();
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
                if ($field instanceof \CoreDB\Kernel\Database\DataType\File) {
                    /** @var InputWidget $widget*/
                    $widget->addFileKey(
                        $this->entityName,
                        $this->ID->getValue(),
                        $field_name,
                        $field->isNull
                    );
                }
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
        $deleteButton = Link::create(
            "#",
            TextElement::create(
                "<i class='fa fa-times text-danger core-control'></i> 
                <span class='sr-only'>" . Translation::getTranslation("delete") . "</span>"
            )->setIsRaw(true)
        )->addClass("me-2");
        if ($this instanceof DynamicModel) {
            $deleteButton->addClass("rowdelete")
            ->addAttribute("data-table", $this->getTableName())
            ->addAttribute("data-id", $row["edit_actions"]);
        } else {
            $removeKeyJwt = new JWT();
            $removeKeyJwt->setPayload([
                "entity" => $this->entityName,
                "id" => $row["edit_actions"]
            ]);
            $deleteButton->addClass("entityrowdelete")
            ->addAttribute("data-entity-name", Translation::getTranslation(
                $this->entityName
            ))->addAttribute("data-key", $removeKeyJwt->createToken());
        }
        $row["edit_actions"] = ViewGroup::create("div", "d-flex px-2")
            ->addField(
                $deleteButton
            )->addField(
                ViewGroup::create("a", "ms-2")
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
            ViewGroup::create("a", "btn btn-sm btn-primary me-1 text-white")
                ->addField(
                    ViewGroup::create("i", "fa fa-plus")
                )->addAttribute(
                    "href",
                    $this->entityName ? InsertController::getUrl() . $this->entityName :
                    TableInsertController::getUrl() . $this->getTableName()
                )
                ->addField(TextElement::create(Translation::getTranslation("add")))
        ];
    }
    public function editUrl($value = null)
    {
        if (!$value) {
            $value = $this->ID->getValue();
        }
        $isEntity = static::class != DynamicModel::class;
        return BASE_URL . "/admin/" .
        ($isEntity ? "entity" : "table") . "/insert/" .
        ($isEntity ? $this->entityName : $this->getTableName()) . "/{$value}";
    }

    public function unsetField($fieldName)
    {
        if ($this->$fieldName instanceof DataTypeFile) {
            $file = File::get($this->$fieldName->getValue());
            if ($file) {
                $this->$fieldName->setValue(null);
                $this->save();
                $file->delete();
            }
        }
    }
}
