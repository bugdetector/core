<?php function get_field_row(int $index,array $definition = NULL, string $table = NULL) {
    $data_types = [];
    $selected = NULL;
    $description = $definition[3] == "MUL" ? get_foreign_key_description($table, $definition[0])->fetch(PDO::FETCH_NUM) : NULL;
    foreach (CoreDB::get_supported_data_types() as $key => $value){
        $data_types[$key] = $value["value"];
        if($value["selected_callback"]($definition)["checked"]){
            $selected = $key;
        }
    }
    return [
        "<span class='glyphicon glyphicon-remove core-control ".($definition == NULL ? "removefield" : "dropfield")."'></span>",
        "<input type='text' class='form-control lowercase_filter' name='fields[$index][field_name]' ".($definition ? "value='$definition[0]' disabled" : " ")."/>",
        SelectField::prepare_select_box($data_types, [
            "name" => "fields[$index][field_type]", 
            "default_value" => $selected,
            "classes" => ["type-control"],
            "attributes" => $selected ? ["disabled" => TRUE] : NULL]),
        "<input type='checkbox' class='pull-right' name='fields[$index][is_unique]' ".(strpos($definition[3] ,"UNI") !==FALSE ? "checked" : "")." ".($definition ? "disabled" : "")." value='1'/>",
        strpos($definition[1], "varchar") === 0 ? _t(62) : (strpos ($definition[3] ,"MUL") !== FALSE ? _t(63) : " "),
        strpos($definition[1], "varchar") === 0 ? filter_var($definition[1], FILTER_SANITIZE_NUMBER_INT) : (isset ($description) && $description ? $description[0] : " ")
    ];
}