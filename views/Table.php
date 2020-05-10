<?php

class Table extends View
{
    private $table_data;
    private $table_headers;
    private $orderable = false;
    public function __construct(array $table_headers, array $table_data)
    {
        $this->table_data = $table_data;
        $this->table_headers = $table_headers;
        $this->classes = ["table", "table-bordered", "text-gray-900"];
    }

    public function setOrderable(bool $orderable)
    {
        $this->orderable = $orderable;
        return $this;
    }

    public function render()
    { ?>
        <div class="table_wrapper">
            <table class="<?php echo $this->renderClasses(); ?>" <?php echo $this->renderAttributes(); ?>>
                <thead>
                    <?php array_walk($this->table_headers, function ($th, $key) { ?>
                        <th><?php if ($this->orderable && !is_numeric($key)) {
                                $params = array_filter($_GET);
                                if(isset($params["orderby"]) && $params["orderby"] == $key){
                                    $new_orderdirection =  isset($_GET["orderdirection"])  && $_GET["orderdirection"] == "DESC" ? "ASC" : "DESC";
                                    $order_icon = "<i class='".($new_orderdirection == "ASC" ? "fa fa-sort-up" : "fa fa-sort-down")."'></i>";
                                }else{
                                    $new_orderdirection = "DESC";
                                    $order_icon = "<i class='fa fa-sort'></i>";
                                }
                                $params["orderby"] = $key;
                                $params["orderdirection"] = $new_orderdirection;
                                echo "<a href='?".http_build_query($params)."'>$th $order_icon</a></th>";
                            } else {
                                echo $th;
                            } ?></th>
                    <?php }) ?>
                </thead>
                <tbody>
                    <?php array_walk($this->table_data, function ($tr) { ?>
                        <tr>
                            <?php array_walk($tr, function ($td) { ?>
                                <td><?php echo $td; ?></td>
                            <?php }) ?>
                        </tr>
                    <?php }) ?>
                </tbody>
            </table>
        </div>
<?php }
}
