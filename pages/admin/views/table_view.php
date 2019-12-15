<?php

function echo_table(array $table_headers, array $table_content, array $options = [] ) { 
    $prepend_element = isset($options["prepend_element"]) ? $options["prepend_element"] : NULL;
    $append_element = isset($options["append_element"]) ? $options["append_element"]: NULL;
    $print_line_number = isset($options["print_line_number"]) ? $options["print_line_number"]: NULL;
    $orderable = isset($options["orderable"]) ? $options["orderable"] : NULL; ?>
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
                $orderdirection = $_GET["orderby"] == $key && $_GET["orderdirection"] == "DESC" ? "ASC" : "DESC";
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
<?php }
