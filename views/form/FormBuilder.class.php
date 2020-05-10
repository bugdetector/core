<?php

class FormBuilder extends FieldControl
{
    private $method;
    private $fields = [];
    private $enctype = NULL;

    public function __construct(string $method = "GET", array $fields = [])
    {
        $this->method = $method;
        foreach ($fields as $field) {
            $this->addField($field);
        }
        $this->removeClass("form-control");
    }

    public function addField(View $field, $offset = 0): self
    {
        if (!$offset) {
            $this->fields[] = $field;
        } else {
            array_splice($this->fields, $offset, 1, [$field, $this->fields[$offset]]);
        }
        return $this;
    }

    public function getField($offset): View
    {
        return $this->fields[$offset];
    }

    public function setEnctype(string $enctype): self
    {
        $this->enctype = $enctype;
        return $this;
    }

    public function render()
    { ?>
        <form method='<?php echo $this->method; ?>' <?php echo $this->enctype ? "enctype='$this->enctype'" : ""; ?> class='<?php echo $this->renderClasses(); ?>'>
            <?php $this->renderFields(); ?>
        </form>
<?php }

    private function renderFields()
    {
        foreach ($this->fields as $field) {
            $field->render();
        }
    }
}
