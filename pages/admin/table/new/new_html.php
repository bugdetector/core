<div class="container  scroll">
    <?php if($this->request_table){
            echo "<a href='".BASE_URL."/admin/table/{$this->request_table}'class='btn btn-outline-info mt-4 mb-4'><span class='glyphicon glyphicon-chevron-left'></span>Back to table</a>";
    } ?>
    <?php $this->printMessages(); ?>
    <form class="container-fluid text-center" id="new_table" method="post">
        <input type="text" name="form_build_id" value="<?php echo $this->form_build_id; ?>" class="d-none" />
        <div class="row ml-1">
          <div class="col-xs-6 form-group has-error">
              <input class="form-control lowercase_filter" type="text" name="table_name" placeholder="<?php echo _t(60); ?>" <?php echo $this->request_table ? "value='$this->request_table' disabled" : "autofocus";?>/>
          </div>
        </div>
        <?php 
            $table_headers = ["#", _t(57), _t(58), _t(59)];
            $table_data = [];
            $this->import_view("field_definition");
            if($this->request_table){
                $definitions = CoreDB::get_table_description($this->request_table);
                foreach ($definitions as $index => $definition){
                    $table_data[] = get_field_row($index,$definition, $this->request_table);
                }
            }else {
                $table_data[] = get_field_row(0);
            }            
            $this->import_view("table_view");
            echo_table($table_headers, $table_data);
        ?>
        </table>
        <div class="row mt-4">
            <div class="col-sm-3">
                <input type="button" class="form-control btn btn-info newfield" value="<?php echo _t(61); ?>"/> 
            </div>
            <div class="col-sm-3">
                <input type="submit" class="form-control btn btn-primary"  name="<?php echo $this->request_table ? "alter_table":"save_table" ?>" value="<?php echo _t(37); ?>"/>
            </div>
            <?php if($this->request_table){ ?>
            <div class="col-sm-3">
                <a href="<?php echo SITE_ROOT; ?>/admin/table/new" class="btn btn-warning form-control" ><?php echo _t(13); ?></a>
            </div>
            <div class="col-sm-3">
                <a href="<?php echo SITE_ROOT."/admin/table/dbobject/$this->request_table"; ?>" class="btn btn-success form-control" >DBObject >></a>
            </div>
            <?php } ?>
        </div>

    </form>
</div>


