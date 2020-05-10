<?php

class TextareaField extends FieldControl{
    public function render() { ?>
        <label><?php echo $this->label ?></label>
        <textarea class="<?php echo $this->renderClasses() ?>" name="<?php echo $this->name; ?>" <?php echo $this->renderAttributes(); ?>><?php echo $this->value; ?></textarea>
    <?php }

}
