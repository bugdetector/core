<?php

class SelectField extends FieldControl{
    private $options = [];

    public function __construct(string $name) {
        parent::__construct($name);
        $this->addClass("selectpicker");
    }
    public function setOptions(array $options) : self{
        $this->options = $options;
        return $this;
    }

    public function renderField() : string{
        return (isset($this->label) ? "<label>{$this->label}</label>" : "").
        self::prepare_select_box($this->options, [
            "name" => $this->name,
            "classes" => $this->classes,
            "null_element" => _t(116),
            "default_value" => $this->value,
            "attributes" => $this->attributes
        ]);
    }
    
    public static function prepare_select_box_from_query_result(PDOStatement $result, array $options){
        $result_array = $result->fetchAll(PDO::FETCH_NUM);
        $select_array = [];
        foreach ($result_array as $count => $row) {
            $select_array[$row[0]] = $row[1];
        }
        return self::prepare_select_box($select_array, $options);
    }


    public static function prepare_select_box(array $elements, array $options){
        $name = isset($options["name"]) ? $options["name"] : "";
        $null_element = isset($options["null_element"]) ? $options["null_element"]: "";
        $classes = isset($options["classes"]) ? $options["classes"] : [];
        $default_value = isset($options["default_value"]) ? $options["default_value"] : "";
        $attributes = isset($options["attributes"]) ? $options["attributes"] : [];
        $attributes["data-live-search"] = isset($attributes["data-live-search"]) ? $attributes["data-live-search"] : true;
        $attributes_out = "";
        foreach($attributes as $attribute => $value){
            $attributes_out.= " $attribute='$value' ";
        }
        $out = "<select name='$name' class='selectpicker "
        .implode(" ",$classes)."' $attributes_out >".
        ($null_element ? "<option value='NULL'>$null_element</option>" : "");

        foreach($elements as $key => $value){
            $out.="<option value='$key' ".($key == $default_value ? "selected" : "").">$value</option>";
        }
        $out.="</select>";
        return $out;
    }
}