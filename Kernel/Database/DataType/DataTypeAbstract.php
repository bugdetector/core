<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Form\Widget\FormWidget;

abstract class DataTypeAbstract
{
    protected $value = "";
    public string $column_name;
    public bool $primary_key = false;
    public bool $autoIncrement = false;
    public bool $isNull = true;
    public bool $isUnique = false;
    public $default;
    public string $comment = "";

    public function __construct(string $column_name)
    {
        $this->column_name = $column_name;
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getName();
    }

    /**
     * @return string
     *  Human readaple name of type
     */
    abstract public static function getText(): string;

    /**
     * @return FormWidget
     *  Available form widget for this data type
     */
    abstract public function getWidget(): FormWidget;

    /**
     * @return FormWidget
     *  Available form widget for search form
     */
    abstract public function getSearchWidget(): ?FormWidget;

    /**
     * Return this object equals $dataType
     * @param DataTypeAbstract $dataType
     * @return bool
     *  Equals
     */
    public function equals(DataTypeAbstract $dataType): bool
    {
        $equals = get_class($dataType) == get_class($this);
        if ($equals) {
            foreach ($this as $field_name => $field) {
                if ($field_name == "default") {
                    continue;
                }
                if ($field != $dataType->{$field_name}) {
                    $equals = false;
                    break;
                }
            }
        }
        return $equals;
    }

    public function toArray(): array
    {
        $array = [];
        $array["type"] = array_search(get_class($this), \CoreDB::database()->dataTypes());
        foreach ($this as $field_name => $field) {
            if ($field_name == "value") {
                continue;
            }
            $array[$field_name] = $field;
        }
        return $array;
    }

    /**
     * Set value.
     */

    public function setValue($value)
    {
        $this->value = \CoreDB::cleanXSS($value);
    }

    /**
     * Return value.
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return strval($this->getValue());
    }
}
