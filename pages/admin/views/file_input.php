<?php 
function echo_file_input($file_field, $file_name = "") {?>
<div >
    <div class="btn btn-success col-sm-2 col-xs-12 file-field">
        Dosya Seç
    </div>
    <input type="file" name="<?php echo $file_field;?>" style="display: none;"/>
    <div class="col-sm-10 col-xs-12">
        <input class="file-path form-control" type="text" value="<?php echo $file_name; ?>" placeholder="Dosya yükleyin"/>
    </div>
</div>
<?php }