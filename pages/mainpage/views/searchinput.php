<?php function echo_search_input(string $form_build_id) { ?>
    <form method="POST" class="input-group">
        <input type="text" name="search_param" class="form-control" placeholder="<?php echo _t(55); ?>"/>
        <span class="input-group-btn">
            <button class="btn btn-default" name="search_action" type="submit"><span class="glyphicon glyphicon-search"></span></button>
        </span>
        <input type="text" class="hidden" name="form_build_id" value="<?php echo $form_build_id; ?>"/>
    </form>
<?php 
}