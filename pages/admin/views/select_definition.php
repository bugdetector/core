<?php 
function echo_select_definition($definition = NULL) { ?>
    <div class="row content select_definition">
    <div class="col-xs-1">
        
        <span class="glyphicon glyphicon-remove core-control removefield"></span>
        
    </div>
    <div class="col-xs-2">
        <select class="form-control selectpicker table_selector" data-live-search="true" name="table">
            <?php
            $tables = get_information_scheme();
            foreach ($tables  as $table){
                ?> <option value="<?php echo $table;?>" <?php echo $definition && $definition->table == $table ? "selected" : "" ?>><?php echo $table;?></option>
          <?php
            }
          ?>
        </select>
    </div>
    <div class="col-xs-2">
        <select class="form-control selectpicker" data-live-search="true" name="column">
            <option value="*">Hepsi</option>
            <?php
            if($definition){
                $table = $definition->table;
            }else{
                $table = $tables[0];
            }
            $fields = get_table_description($table);
            foreach ($fields as $field){
                ?> <option value="<?php echo $field[0];?>" <?php echo $definition && $definition->column == $field[0] ? "selected" : "" ?>><?php echo $field[0];?></option>
          <?php
            }
          ?>
        </select>
    </div>
</div>
<?php }
