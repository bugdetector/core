<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $this->title; ?></h1>
    </div>
    <?php
    /**
     * @var AdminTableEditController $this
     */
    if ($this->request_table) {
        $link = new Group("btn btn-outline-info mt-4 mb-4");
        $link->setTagName("a")
        ->addAttribute("href", BASE_URL."/admin/table/{$this->request_table}")
        ->addField(
            Group::create("fa fa-chevron-left")->setTagName("i")
        )->addField(
            TextElement::create(" "._t("back_table"))
        );
        echo $link;
    } ?>
    <?php $this->printMessages(); ?>
    <form class="text-center" id="new_table" method="post">
        <input type="hidden" name="form_build_id" value="<?php echo $this->form_build_id; ?>" />
        <input type="hidden" name="form_token" value="<?php echo $this->form_token; ?>" />
        <div class="row ml-1">
            <div class="col-md-3 col-sm-6 col-xs-12 form-group text-left font-weight-bold <?php echo $this->request_table ? : "has-error"; ?>">
                <label for="table_name" ><?php echo _t("table_name"); ?></label>
                <input class="form-control lowercase_filter" id="table_name" type="text" name="table_name" placeholder="<?php echo _t("table_name"); ?>" <?php echo $this->request_table ? "value='$this->request_table' disabled" : "autofocus"; ?> />
            </div>
            <div class="col-md-9 col-sm-12 form-group text-left font-weight-bold">
                <label for="table_comment" ><?php echo _t("table_comment"); ?></label>
                <input class="form-control" id="table_comment" type="text" name="table_comment" placeholder="<?php echo _t("table_comment"); ?>" <?php echo $this->request_table ? "value='$this->table_comment' disabled" : "autofocus"; ?> />
            </div>
        </div>
        <?php
        $header_row = new Group("row bg-gray-300 text-gray-900 font-weight-bold pt-3 pb-3");
        foreach (["", _t("column_name"), _t("data_type"), _t("unique"), ""] as $header) {
            $header_row->addField(
                Group::create("col")->addField(
                    TextElement::create($header)
                )
            );
        }
        echo $header_row;
        $fields = Group::create("w-100");
        if ($this->request_table) {
            foreach (CoreDB::get_table_description($this->request_table) as $index => $description) {
                $field_view = new FieldDefinitionRow($description);
                $field_view->setIndex($index);
                $field_view->setTable($this->request_table);
                $fields->addField($field_view);
            }
        } else {
            $field_view = new FieldDefinitionRow();
            $fields->addField($field_view);
        }
        echo $fields;
        ?>
        </table>
        <div class="row mt-4 mb-5">
            <div class="col-sm-3">
                <input type="button" class="form-control btn btn-info newfield" value="<?php echo _t("new_field"); ?>" />
            </div>
            <div class="col-sm-3">
                <input type="submit" class="form-control btn btn-primary" name="<?php echo $this->request_table ? "alter_table" : "save_table" ?>" value="<?php echo _t("save"); ?>" />
            </div>
            <?php if ($this->request_table) { ?>
                <div class="col-sm-3">
                    <a href="<?php echo BASE_URL . "/admin/table/dbobject/$this->request_table"; ?>" class="btn btn-success form-control">DBObject <i class="fa fa-angle-double-right"></i> </a>
                </div>
            <?php } ?>
        </div>

    </form>
</div>