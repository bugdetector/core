<div class="container container-fluid">
    <?php $this->printMessages(); ?>
    <div class="container-fluid text-left">
        <?php if($this->table){
            echo "<a href='".BASE_URL."/admin/table/{$this->table}'class='btn btn-outline-info mt-4 mb-4'><span class='glyphicon glyphicon-chevron-left'></span>Back to table</a>";
        } ?>
        <?php echo $this->form->build(); ?>
    </div>
</div>