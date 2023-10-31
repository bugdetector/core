<?php

namespace CoreDB\Kernel;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use PDO;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Form\Widget\CollapsableWidgetGroup;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\OptionWidget;
use Src\Form\Widget\SelectWidget;

class EntityReference extends DataTypeAbstract
{
    public const CONNECTION_MANY_TO_MANY = "manyToMany";
    public const CONNECTION_MANY_TO_ONE = "manyToOne";
    public const CONNECTION_ONE_TO_MANY = "oneToMany";
    public const CONNECTION_ONE_TO_ONE = "oneToOne";

    public Model $object;
    public string $fieldEntityName;
    public string $connectionType;
    public string $mergeTable;
    public string $selfKey;
    public string $foreignKey;
    public bool $createIfNotExist = false;

    public function __construct(string $fieldEntityName, Model &$object, array $config, string $connectionType)
    {
        $this->fieldEntityName = $fieldEntityName;
        $this->object = $object;
        $this->connectionType = $connectionType;
        if ($connectionType == self::CONNECTION_MANY_TO_MANY) {
            $this->mergeTable = $config["mergeTable"];
            $this->selfKey = $config["selfKey"];
            $this->foreignKey = $config["foreignKey"];
            $this->createIfNotExist = @$config["createIfNotExist"] ?: false;
            $this->value = $this->getCheckeds();
        } elseif ($connectionType == self::CONNECTION_ONE_TO_MANY) {
            $this->foreignKey = $config["foreignKey"];
            $this->createIfNotExist = @$config["createIfNotExist"] ?: false;
            $this->value = [];
            foreach ($this->getCheckeds() as $data) {
                $this->value[] = $data->toArray();
            }
        } elseif ($connectionType == self::CONNECTION_ONE_TO_ONE) {
            $this->foreignKey = $config["foreignKey"];
            $this->createIfNotExist = @$config["createIfNotExist"] ?: false;
            $this->value = [];
            foreach ($this->getCheckeds() as $data) {
                $this->value[] = $data->toArray();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = is_array($value) ? array_filter($value) : $value;
    }

    /**
     * Pseudo function. There is no use.
     * @inheritdoc
     */
    public static function getText(): string
    {
        return "";
    }

    public function getWidget(): FormWidget
    {
        $widget = null;
        if ($this->connectionType == self::CONNECTION_MANY_TO_MANY) {
            $checkeds = $this->getCheckeds();

            $referenceClass = \CoreDB::config()->getEntityInfo($this->fieldEntityName)["class"];
            $allOptions = \CoreDB::database()->select($referenceClass::getTableName())
                ->select("", ["ID"])
                ->limit(100)
                ->execute()->fetchAll(PDO::FETCH_COLUMN);
            $allOptions = array_unique(array_merge($checkeds, $allOptions));

            $options = [];
            foreach ($allOptions as $anOption) {
                $object = $referenceClass::get($anOption);
                if ($object) {
                    $objectArray = $object->toArray();
                    $text = current($objectArray);
                    $option = new OptionWidget($anOption, $text);
                    if (in_array($anOption, $checkeds)) {
                        $option->setSelected(true);
                    }
                    $options[] = $option;
                }
            }
            $widget = SelectWidget::create("")
            ->setNullElement(null)
            ->addAttribute("multiple", "multiple")
            ->setOptions($options)
            ->setAutoComplete($referenceClass::getTableName(), array_keys(
                (new $referenceClass())->toArray()
            )[0])
            ->createIfNotExist($this->createIfNotExist);
        } elseif (
            $this->connectionType == self::CONNECTION_ONE_TO_MANY ||
            $this->connectionType == self::CONNECTION_ONE_TO_ONE
        ) {
            $widget = CollapsableWidgetGroup::create($this->object->entityName, $this->fieldEntityName);
            /** @var Model $referenceClass */
            $referenceClass = \CoreDB::config()->getEntityInfo($this->fieldEntityName)["class"];
            if ($this->connectionType == self::CONNECTION_ONE_TO_MANY) {
                $widget->setHiddenFields([
                    $this->foreignKey
                ]);
                $checkeds = $this->getCheckeds();
                if ($checkeds) {
                    foreach ($checkeds as $index => $object) {
                        $widget->addCollapsibleObject($object, $index + 1);
                    }
                } else {
                    /**
                     * This action is only done for adding depended js codes.
                     * @var Model $object
                     */
                    $object = new $referenceClass();
                    $object->getForm();
                }
            } else {
                /** @var Model $object */
                $object = $referenceClass::get([
                    $this->foreignKey => $this->object->ID->getValue()
                ]) ?: new $referenceClass();
                $widget->hiddenFields = [$this->foreignKey];
                $widget->showAddButtonAndLabel = false;
                $widget->addCollapsibleObject($object, 1, true);
                $widget->fieldGroup->fields[0]->title = Translation::getTranslation($this->fieldEntityName);
                $widget->addClass("mt-2");
            }
        }
        return $widget;
    }

    public function getSearchWidget(): ?FormWidget
    {
        if ($this->connectionType == self::CONNECTION_MANY_TO_MANY) {
            return $this->getWidget();
        }
        return null;
    }

    public function getCheckeds(): array
    {
        if ($this->connectionType == self::CONNECTION_MANY_TO_MANY) {
            return \CoreDB::database()->select($this->mergeTable)
            ->select("", [$this->foreignKey])
            ->condition($this->selfKey, $this->object->ID->getValue())
            ->execute()->fetchAll(PDO::FETCH_COLUMN);
        } elseif (
            $this->connectionType == self::CONNECTION_ONE_TO_MANY ||
            $this->connectionType == self::CONNECTION_ONE_TO_ONE
        ) {
            /** @var Model */
            $referenceClass = \CoreDB::config()->getEntityInfo($this->fieldEntityName)["class"];
            return $referenceClass::getAll([
                $this->foreignKey => $this->object->ID
            ]);
        }
    }

    public function save()
    {
        if ($this->connectionType == self::CONNECTION_MANY_TO_MANY) {
            if (!$this->value) {
                $this->value = [];
            }
            $checkeds = $this->getCheckeds();
            foreach ($this->value as $index => $value) {
                if (isset($checkeds[$index])) {
                    $object = DynamicModel::get([
                        $this->selfKey => $this->object->ID->getValue(),
                        $this->foreignKey => $checkeds[$index]
                    ], $this->mergeTable);
                } else {
                    $object = new DynamicModel($this->mergeTable);
                    if (!is_numeric($value)) {
                        $referenceClass = \CoreDB::config()->getEntityInfo($this->fieldEntityName)["class"];
                        $referenceFieldName = array_keys(
                            (new $referenceClass())->toArray()
                        )[0];
                        $referenceObject = new $referenceClass();
                        $referenceObject->map([
                            $referenceFieldName => $value
                        ]);
                        $referenceObject->save();
                        $value = $referenceObject->ID->getValue();
                    }
                }
                $object->map([
                    $this->selfKey => $this->object->ID->getValue(),
                    $this->foreignKey => $value
                ]);
                $object->save();
            }
            if (isset($index)) {
                $index++;
            } else {
                $index = 0;
            }
            if (isset($checkeds[$index])) {
                for ($index; $index < count($checkeds); $index++) {
                    $object = DynamicModel::get([
                        $this->selfKey => $this->object->ID->getValue(),
                        $this->foreignKey => $checkeds[$index]
                    ], $this->mergeTable);
                    $object->delete();
                }
            }
        } elseif (
            $this->connectionType == self::CONNECTION_ONE_TO_MANY ||
            $this->connectionType == self::CONNECTION_ONE_TO_ONE
        ) {
            $referenceClass = \CoreDB::config()->getEntityInfo($this->fieldEntityName)["class"];
            $existing = $this->getCheckeds();
            if ($this->value) {
                foreach ($this->value as $data) {
                    if (!empty($existing)) {
                        /** @var Model */
                        $object = array_shift($existing);
                    } else {
                        /** @var Model */
                        $object = new $referenceClass();
                        $data[$this->foreignKey] = $this->object->ID->getValue();
                    }
                    $object->map($data);
                    $object->save();
                }
            }
            foreach ($existing as $remaining) {
                $remaining->delete();
            }
        }
    }
}
