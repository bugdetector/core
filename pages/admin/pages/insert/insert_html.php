<?php
function echo_insert_page(InsertController $controller){
    $controller->import_view("file_input");
    $description = get_table_description($controller->table);
    unset($description[0]);
    $object = $controller->object;
    ?>
    <div class="container container-fluid">
        <?php $controller->printMessages(); ?>
        <form class="container-fluid text-center" method="POST" enctype="multipart/form-data">
            <div class="row content">
                <div class="col-sm-3 text-left">
                    ID
                </div>

                <div class="col-sm-9 text-left">
                    <input type="number" class="form-control" disabled value="<?php echo get_field_from_object($object, "ID"); ?>" />
                </div>
          </div>
        <?php
        $supported_data_types = get_supported_data_types();
        foreach ($description as $desc){
        ?>
        <div class="row content">
              <div class="col-sm-3 text-left">
                  <?php echo $desc[0]; ?>
              </div>
            
              <div class="col-sm-9 text-left">
               <?php echo $supported_data_types[get_input_type($desc[1], $desc[3])]["input_field_callback"]($object, $desc, $controller->table); ?>
            </div>
        </div>
        <?php } ?>  
             <?php if($object && $object->ID){
                ?>
            <div class="row content" style="margin-bottom: 5px;">
                <div class="col-sm-6">
                    <input type="submit" class="btn btn-warning form-control" name="update?" value="<?php echo _t(85); ?>"/> 
                </div>
                <div class="col-sm-6">
                    <input type="button" class="btn btn-danger form-control recordelete" value="<?php echo _t(82); ?>"/>
                    <input type="submit" class="btn btn-danger form-control hidden" name="delete?"/>
                </div>
            </div>
            <?php } ?>
            <div class="row content">
                <div class="col-sm-6">
                    <input type="submit" class="btn btn-primary form-control" name="insert?" value="<?php echo _t(14); ?>"/> 
                </div>
                 <?php if(!$object || !$object->ID){
                ?>
                <div class="col-sm-6">
                    <input type="reset" class="btn btn-danger form-control" value="<?php echo _t(84); ?>"/>
                </div>                
                <?php } ?>
            </div>
        </form>
    </div>
    <?php
}

function get_input_type(string $dataType, $key = ""){
    if($key == "MUL"){
        return $key;
    }elseif(strpos($dataType, "int") === 0){
        return "INT";
    }elseif (strpos($dataType, "varchar") === 0) {
        return "VARCHAR";
    }else {
        return strtoupper($dataType);
    }
}

function get_field_from_object(&$object, $field) {
    return isset($object->$field) ? $object->$field : "";
}


