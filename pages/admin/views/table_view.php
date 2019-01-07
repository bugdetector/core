<?php

function echo_table(array $table_headers, array $table_content, string $prepend_element = "", string $appent_element = "" ) { ?>
<table class="content" id="result_table">
    <thead>
        <tr class="head">
        <?php
        echo $prepend_element ? "<td></td>" : "";
        foreach ($table_headers as $header) {
            echo "<td>$header</td>";
        } 
        echo $appent_element ? "<td></td>" : "";
        ?>
        </tr>
    </thead>
    <tbody>
        <?php 
        foreach ($table_content as $content) {
            echo "<tr>";
            echo $prepend_element ? "<td>$prepend_element</td>": "";
            foreach ($content as $value) {
                echo "<td>$value</td>";
            }
            echo $appent_element ? "<td>$appent_element</td>": "";
            echo "</tr>";
        } ?>
    </tbody>
</table>
<?php }
