<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Form\Widget\FormWidget;

abstract class DataTypeAbstract
{
    public string $column_name;
    public bool $primary_key = false;
    public bool $autoIncrement = false;
    public bool $isNull = false;
    public bool $isUnique = false;
    public $default;
    public string $comment = "";

    public function __construct(string $column_name)
    {
        $this->column_name = $column_name;
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getName();;
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
    abstract public function getSearchWidget(): FormWidget;

    /**
     * Return this object equals $dataType
     * @param DataTypeAbstract $dataType
     * @return bool
     *  Equals
     */
    public function equals(DataTypeAbstract $dataType): bool {
        return get_class($dataType) == get_class($this) &&
            $this->column_name == $dataType->column_name &&
            $this->primary_key == $dataType->primary_key &&
            $this->autoIncrement == $dataType->primary_key &&
            $this->isNull == $dataType->isNull &&
            $this->isUnique == $dataType->isUnique &&
            $this->comment == $dataType->comment;
    }
}
