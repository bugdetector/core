<?php
$this->import_view("file_input");
?>
<div class="container container-fluid">
    <?php $this->printMessages(); ?>
    <div class="container-fluid text-left">
        <?php echo $this->form->build(); ?>
    </div>
</div>