<?php

class SideBarTableList extends View
{
    private $active_table;
    public function __construct($active_table = "")
    {
        $this->active_table = $active_table;
    }

    public function render()
    { ?>


        <div class="col-md-4">
            <div class="card shadow mb-4">
                <!-- Card Header - Accordion -->
                <a href="#table_list" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo _t("tables"); ?></h6>
                </a>
                <!-- Card Content - Collapse -->
                <div class="collapse show" id="table_list">
                    <div class="card-body">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control lowercase_filter" placeholder="<?php echo _t("search"); ?>" id="table_search_field">
                        </div>
                        <div class="list-group">
                            <?php
                            foreach (CoreDB::get_information_scheme() as $table_name) {
                            ?>
                                <div class="btn-group mb-1 table_info">
                                    <a href="<?php echo SITE_ROOT . "/admin/table/$table_name"; ?>" class="btn btn-outline-dark text-left <?php echo $this->active_table == $table_name ? "active" : ""; ?>">
                                        <span class="fa fa-table"></span> <?php echo $table_name; ?>
                                    </a>
                                    <button type="button" class="btn btn-info dropdown-toggle dropdown-toggle-split" 
                                    style="max-width: min-content;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    </button>
                                    <div class="dropdown-menu bg-gradient-info">
                                        <a class="dropdown-item" href="<?php echo BASE_URL . "/admin/table/insert/$table_name"; ?>">
                                            <i class="fa fa-save"></i><?php echo _t("add"); ?>
                                        </a>
                                        <a class="dropdown-item" href="<?php echo BASE_URL . "/admin/table/edit/$table_name"; ?>">
                                            <i class="fa fa-edit"></i><?php echo _t("edit"); ?>
                                        </a>
                                        <a class="dropdown-item tabletruncate" data-table-name="<?php echo $table_name; ?>" href="#">
                                            <i class="fa fa-trash"></i><?php echo _t("truncate"); ?>
                                        </a>
                                        <a class="dropdown-item tabledrop" href="#" data-table-name="<?php echo $table_name; ?>">
                                            <i class="fa fa-times-circle"></i><?php echo _t("drop_table"); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php }
}
