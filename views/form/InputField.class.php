<?php
class InputField extends FieldControl
{
    private $type = "text";

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function render()
    { ?>
        <label <?php echo isset($this->attributes["id"]) ? "for='" . $this->attributes["id"] . "'" : ""; ?>>
            <?php echo $this->label; ?>
        </label>
        <input type='<?php echo $this->type; ?>' name='<?php echo $this->name; ?>' class='<?php echo $this->renderClasses(); ?>' <?php echo $this->renderAttributes(); ?> value='<?php echo $this->value; ?>' />
<?php }
}
