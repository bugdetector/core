<?php 
function echo_comparation_definition($definition = NULL) {?>
    <div class="row content comparation_definition">
    <div class="col-xs-1">        
        <span class="glyphicon glyphicon-remove core-control removefield"></span>
        <span class="glyphicon glyphicon-arrow-down core-control chosefield" title="Bu alanı seçime taşı"></span>        
    </div>
    <div class="col-xs-2">
        <select class="form-control selectpicker table_selector" data-live-search="true" name="first_table">
            <?php
            $tables = get_information_scheme();
            foreach ($tables  as $table){
                ?> <option value="<?php echo $table;?>" <?php echo $definition && $definition->first_table == $table ? "selected" : "";?>><?php echo $table;?></option>
          <?php
            }
          ?>
        </select>
    </div>
    <div class="col-xs-2">
        <select class="form-control selectpicker" data-live-search="true" name="first_table_column">
            <?php
            if($definition){
                $first_table = $definition->first_table;
            }else{
                $first_table = $tables[0];
            }
            $fields = get_table_description($first_table);
            foreach ($fields as $field){
                ?> <option value="<?php echo $field[0];?>" <?php echo $definition && $definition->first_table_column == $field[0] ? "selected" : "";?>><?php echo $field[0];?></option>
          <?php
            }
          ?>
        </select>
    </div>
    <div class="col-xs-1">
        <select class="selectpicker" name="comparation" data-width="75px">
            <option value="=" <?php echo $definition && $definition->comparation == "=" ? "selected" : "";?> >=</option>
            <option value="!=" <?php echo $definition && $definition->comparation == "!=" ? "selected" : "";?> >!=</option>
            <option value=">" <?php echo $definition && $definition->comparation == ">" ? "selected" : "";?>>></option>
            <option value="<" <?php echo $definition && $definition->comparation == "<" ? "selected" : "";?>><</option>
            <option value=">=" <?php echo $definition && $definition->comparation == ">=" ? "selected" : "";?>>>=</option>
            <option value="<=" <?php echo $definition && $definition->comparation == "<=" ? "selected" : "";?>><=</option>
            <option value="like" <?php echo $definition && $definition->comparation == "like" ? "selected" : "";?>>Örüntü</option>
            <option value="not like" <?php echo $definition && $definition->comparation == "not like" ? "selected" : "";?>>Örüntü(Değil)</option>
            <option value="in" <?php echo $definition && $definition->comparation == "in" ? "selected" : "";?>>İçinde</option>
            <option value="not in" <?php echo $definition && $definition->comparation == "not in" ? "selected" : "";?>>İçinde Değil</option>
            <option value="regexp" <?php echo $definition && $definition->comparation == "not in" ? "selected" : "";?>>İçende Geçen</option>
            <option value="not regexp" <?php echo $definition && $definition->comparation == "not in" ? "selected" : "";?>>İçinde Geçmeyen</option>
        </select>
    </div>
    <div class="col-xs-1">
        <select class="selectpicker" name="comparation_type" data-width="75px">
            <option value="entry" <?php echo $definition && $definition->comparation_type == "entry" ? "selected" : "";?>>Girilen değer ile kıyas</option>
            <option value="another_table" <?php echo $definition && $definition->comparation_type == "another_table" ? "selected" : "";?>>Başka bir alanla kıyas</option>
        </select>
    </div>
    <div class="col-xs-2 <?php echo ($definition && $definition->comparation_type != "entry") ? "hidden": ""?>">
        <input class="form-control" type="text" name="comparation_entry" value="<?php echo $definition && $definition->comparation_type == "entry" ? $definition->comparation_entry : "";?>"/> 
    </div>
    <div class="col-xs-2 compare_table <?php echo $definition && $definition->comparation_type == "another_table" ? "": "hidden"?>">
        <select class="form-control selectpicker table_selector" data-live-search="true" name="second_table">
        <?php 
        foreach ($tables  as $table){
                ?> <option value="<?php echo $table;?>" <?php echo $definition && $definition->second_table == $table ? "selected" : "";?>><?php echo $table;?></option>
          <?php
            } ?>
        </select>
    </div>
    <div class="col-xs-2 compare_table <?php echo $definition && $definition->comparation_type == "another_table" ? "": "hidden"?>">
        <select class="form-control selectpicker" data-live-search="true" name="second_table_column">
            <?php
            if($definition){
                $second_table = $definition->second_table;
            }else{
                $second_table = $tables[0];
            }
            $fields = get_table_description($second_table);
            foreach ($fields as $field){
                ?> <option value="<?php echo $field[0];?>" <?php echo $definition && $definition->second_table_column == $field[0] ? "selected" : "";?>><?php echo $field[0];?></option>
          <?php
            }
          ?>
        </select>
    </div>
</div>
<?php }