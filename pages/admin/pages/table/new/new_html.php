<?php
function echo_create_table_page(NewTableController $controller){
    ?>
    <div class="container  scroll">
        <?php $controller->printMessages(); ?>
        <form class="container-fluid text-center" id="new_table">
            <input type="text" name="form_build_id" value="<?php echo $controller->form_build_id; ?>" class="hidden" />
            <div class="row content">
              <div class="col-xs-6 form-group has-error">
                  <input class="form-control lowercase_filter" type="text" name="table_name" placeholder="<?php echo _t(60); ?>" <?php echo $controller->request_table ? "value='$controller->request_table' disabled" : "autofocus";?>/>
              </div>
            </div>
            <div class="row content hidden-xs" style="margin-bottom: 10px">
                  <div class="col-sm-1">

                  </div>
                  <div class="col-sm-3">
                      <?php echo _t(57); ?>
                  </div>
                  <div class="col-sm-2">
                      <?php echo _t(58); ?>
                  </div>
                  <div class="col-sm-1">
                      <?php echo _t(59); ?>
                  </div>
            </div>
            <?php $controller->import_view("field_definition");
                if($controller->request_table){
                    $definitions = get_table_description($controller->request_table);
                    foreach ($definitions as $definition){
                        echo_field_definition($definition, $controller->request_table);
                    }
                }else {
                    echo_field_definition();
                }

            ?>
            <div class="row">
                <div class="col-sm-3">
                    <input type="button" class="form-control btn btn-info newfield" value="<?php echo _t(61); ?>"/> 
                </div>
                <div class="col-sm-3">
                    <input type="button" class="form-control btn btn-primary <?php echo $controller->request_table ? "alter_table":"save_table" ?>" value="<?php echo _t(37); ?>"/>
                </div>
                <?php if($controller->request_table){ ?>
                <div class="col-sm-6">
                    <a href="<?php echo SITE_ROOT; ?>/admin/table/new" class="btn btn-warning form-control" ><?php echo _t(13); ?></a>
                </div>
                <?php } ?>
            </div>

        </form>
    </div>
    <?php
}


