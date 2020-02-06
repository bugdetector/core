<?php

function echo_table(array $table_headers, array $table_content, array $options = [] ) { 
    $prepend_element = isset($options["prepend_element"]) ? $options["prepend_element"] : NULL;
    $append_element = isset($options["append_element"]) ? $options["append_element"]: NULL;
    $print_line_number = isset($options["print_line_number"]) ? $options["print_line_number"]: NULL;
    $orderable = isset($options["orderable"]) ? $options["orderable"] : NULL; 
    $filter_options = isset($options["filter_options"]) ? $options["filter_options"] : NULL;
    $show_not_found_message = isset($options["not_found"]) ? $options["not_found"] : TRUE;
    if($filter_options){
        $form_builder = new FormBuilder();
        $form_builder->addClass("row");
        foreach($filter_options as $option){
            $group = new InputGroup("col-md-3 col-sm-4");
            $group->addField(FieldControl::createFromOption($option));
            $form_builder->addField($group);
        } 
            $row = new InputGroup("col-sm-12");
            $search_button = new InputField("");
            $search_button->setType("submit")->setValue(_t(55))->addClass("btn")->addClass("btn-primary")->addAttribute("style","max-width:150px;");
            $row->addField($search_button);
            $clear_button = InputField::create("")->setType("reset")->setValue(_t(84))->addClass("btn-danger")->addClass("form-reset-button")->addAttribute("style","max-width:150px;");
            $row->addField($clear_button);
            $form_builder->addField($row);
        ?>

        <div class="stage align-left">
            <div class="stage_header mt-2">
                <h3><?php echo _t(55); ?></h3>
            </div>
            <div class="stage_content">
                <?php echo $form_builder->build(); ?>
            </div>
        </div>
    <?php } 
    if(count($table_content) || !$show_not_found_message): ?>
<table class="content" id="result_table">
    <thead>
        <tr class="head">
        <?php
        echo $prepend_element ? "<td></td>" : "";
        echo $print_line_number ? "<td>#</td>" : "";
        if($orderable){
            $params = $_GET;
            unset($params["orderby"], $params["orderdirection"]);
        }
        foreach ($table_headers as $key => $header) {
            if($orderable && !is_numeric($key)){
                $orderdirection = isset($_GET["orderby"]) && $_GET["orderby"] == $key && $_GET["orderdirection"] == "DESC" ? "ASC" : "DESC";
                $order_icon = $orderable ? "<span class='".($orderdirection == "DESC" ? "glyphicon glyphicon-chevron-up" : "glyphicon glyphicon-chevron-down")."'></span>" : NULL;
                $params["orderby"] = $key;
                $params["orderdirection"] = $orderdirection;
                echo "<td><a href='?".http_build_query($params)."'>$header $order_icon</a></td>";
            }else{
                echo "<td>$header</td>";
            }
            
        } 
        echo $append_element ? "<td></td>" : "";
        ?>
        </tr>
    </thead>
    <tbody>
        <?php 
        foreach ($table_content as $line_num => $content) {
            echo "<tr>";
            echo $prepend_element ? "<td>$prepend_element</td>": "";
            echo $print_line_number ? "<td>".($line_num+1)."</td>" : "";
            foreach ($content as $value) {
                echo $value !== NULL && $value !== "" ? "<td>$value</td>" : "<td>N/A</td>";
            }
            echo $append_element ? "<td>$append_element</td>": "";
            echo "</tr>";
        } ?>
    </tbody>
</table>
    <?php else: ?>
        <div class="d-flex justify-content-center mt-3">
            <h3><span class="glyphicon glyphicon-search"></span> <?php echo _t(120); ?></h3>
        </div>
    <?php endif;
}
