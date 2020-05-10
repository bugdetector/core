<?php

class SelectField extends FieldControl
{
    private $options = [];
    private $null_element;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->addClass("selectpicker");
        $this->addAttribute("data-container", "container");
        $this->null_element = _t("none");
    }
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function setNullElement(string $null_element)
    {
        $this->null_element = $null_element;
        return $null_element;
    }

    public function render()
    { ?>
        <label><?php echo $this->label; ?></label>
        <select name='<?php echo $this->name;?>' class='<?php echo $this->renderClasses(); ?>' <?php echo $this->renderAttributes(); ?>>
            <?php
            if($this->null_element){
                echo (new OptionField("", $this->null_element));
            }
            foreach ($this->options as $key => $value) {
                if ($value instanceof OptionField) {
                    echo $value;
                } else {
                    echo (new OptionField($key, $value))->setSelected($key == $this->value);
                }
            }
            ?>
        </select>
<?php }
}
