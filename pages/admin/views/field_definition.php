<?php function echo_field_definition(array $definition = NULL, string $table = NULL) {?>
<div class="row content <?php echo $definition ? "": "field_definition";?>" >
    
    <div class="col-sm-1">
        <?php if($definition == NULL){?>
        <span class="glyphicon glyphicon-remove core-control removefield"></span>
        <?php } ?>
    </div>    
    <div class="col-sm-3 has-error">
        <input type="text" class="form-control lowercase_filter" name="field_name" <?php echo $definition ? "value='$definition[0]' disabled" : ""; ?>/>
    </div>
    <div class="col-sm-2">
        <select name="field_type" class="form-control type-control selectpicker" data-live-search="true" <?php echo $definition ? "disabled" : ""; ?>>
            <?php
            foreach (get_supported_data_types() as $key => $value){
                echo "<option value='$key' ".$value["selected_callback"]($definition)["checked"].">".$value["value"]."</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-sm-1">
        <input type="checkbox" name="is_unique" <?php echo strpos($definition[3] ,"UNI") !==FALSE ? "checked" : ""; ?>  <?php echo $definition ? "disabled" : ""; ?>/>
    </div>
    <div class="col-sm-2 optionalexplain">
        <?php if(!$definition){
            echo "";
        }else if(strpos($definition[1], "varchar") === 0){
            echo _t(62);
        }else if(strpos ($definition[3] ,"MUL") !== FALSE ){
            $description = get_foreign_key_description($table, $definition[0])->fetch(PDO::FETCH_NUM);
            echo _t(63);
        } ?>
    </div>
    <div class="col-sm-2 optional" name="optional">
        <?php if(!$definition){
            
        }else if(strpos($definition[1], "varchar") === 0){
            echo filter_var($definition[1], FILTER_SANITIZE_NUMBER_INT);
        }else if(isset ($description) && $description){
                echo "$description[0]";
                $description = FALSE;
        } ?>
    </div>
</div>
<?php }