<?php

class FieldDefinitionRow extends View
{
    private $index = 0;
    private $definition;
    private $table;

    public function __construct($definition = null)
    {
        $this->definition = $definition;
    }
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }
    public function setTable(string $table)
    {
        $this->table = $table;
        return $this;
    }

    public function render()
    { ?>
        <div class="row field_row">
            <div class="col">
                <label class="w-100"></label>
                <span class="text-danger <?php echo $this->definition == NULL ? "removefield" : "dropfield"; ?>">
                    <i class='fa fa-times core-control'></i>
                </span>
            </div>
            <div class="col <?php echo $this->table ? : "has-error"; ?>">
                <label></label>
                <?php 
                $input = new InputField("fields[{$this->index}][field_name]");
                $input->addClass("lowercase_filter")
                ->addAttribute("placeholder", _t("column_name"));
                if($this->definition){
                    $input->setValue($this->definition["Field"])
                    ->addAttribute("disabled", "true");
                }
                echo $input;
                ?>
            </div>
            <div class="col">
                <?php
                $selected = NULL;
                foreach (CoreDB::get_supported_data_types() as $key => $value) {
                    $data_types[$key] = $value["value"];
                    if ($value["selected_callback"]($this->definition)["checked"]) {
                        $selected = $key;
                    }
                }
                $select = new SelectField("fields[{$this->index}][field_type]");
                $select->setNullElement(_t("data_type"));
                $select->addAttribute("required", "true");
                $select->addClass("type-control")->setOptions($data_types);
                if ($selected) {
                    $select->setValue($selected)
                        ->addAttribute("disabled", "true");
                }
                echo $select;
                ?>
            </div>
            <div class="col">
                <label class="w-100"></label>
                <input type='checkbox' class='float-right' name='fields[<?php echo $this->index; ?>][is_unique]' value='1' <?php echo $this->definition ? "disabled" : "";
                                                                                                                            echo " ";
                                                                                                                            echo strpos($this->definition["Key"], "UNI") !== FALSE ? "checked" : ""; ?> />
            </div>
            <div class="col">
                <?php
                if(strpos($this->definition["Type"], "varchar") === 0){
                    $label = _t("length_varchar");
                    $explain = filter_var($this->definition["Type"], FILTER_SANITIZE_NUMBER_INT);
                    echo "<label class='w-100 text-primary'>$label</label>$explain";
                }else if($this->definition["Key"] == "MUL"){
                    $label = _t("reference_table");
                    $explain = CoreDB::get_foreign_key_description($this->table, $this->definition["Field"])[0];
                    echo "<label class='w-100 text-primary'>$label</label>$explain";
                }
                ?>
            </div>
        </div>
<?php }
}
