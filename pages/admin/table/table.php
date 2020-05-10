<?php

class AdminTableController extends AdminController
{
    private $table_name;
    private $table_comment;
    private $table_headers = [""];
    private $table_data;
    private $search_form;
    private $record_count;
    private $page;

    protected function preprocessPage()
    {
        if (!isset($this->arguments[0]) || !$this->arguments[0] || !in_array($this->arguments[0], CoreDB::get_information_scheme())) {
            $this->create_warning_message(_t("table_select_info"), "alert-info");
            $this->setTitle(_t("tables"));
        } else {
            /**
             * Creating table and table search form
             */
            $this->table_name = $this->arguments[0];
            $this->setTitle(_t("tables") . " | {$this->table_name}");
            $this->table_comment = CoreDB::getTableComment($this->table_name);

            //Starting query
            $query = db_select($this->table_name);
            $params = array_filter($_GET);

            $this->search_form = new FormBuilder();
            $this->search_form->addClass("row");
            foreach (CoreDB::get_table_description($this->table_name) as $column) {
                $column_name = $column["Field"];
                $this->table_headers[$column_name] = $column_name;
                $search_input = null;
                if (in_array($column["Type"], ["int", "double"])) {
                    if (CoreDB::is_unique_foreign_key($this->table_name, $column_name)) {
                        /**
                         * Creating referenced table select input for foreign keys
                         */
                        $fk_description = CoreDB::get_foreign_key_description($this->table_name, $column_name);
                        $fk_table = $fk_description["REFERENCED_TABLE_NAME"];
                        $options = [];
                        $first_column_name = CoreDB::get_table_description($fk_table)[1]["Field"];
                        $fk_query = db_select($fk_table);
                        if (isset($params[$column_name])) {
                            $fk_query->condition("ID = :id", [":id" => $params[$column_name]]);
                        } else {
                            $fk_query->limit(AUTOCOMPLETE_SELECT_BOX_LIMIT);
                        }
                        foreach ($fk_query->execute()->fetchAll(PDO::FETCH_OBJ) as $record) {
                            $options[$record->ID] = "{$record->ID} {$record->$first_column_name}";
                        }
                        $search_input = SelectField::create($column_name)
                            ->setLabel($column_name)
                            ->setOptions($options)
                            ->addClass("autocomplete")
                            ->addAttribute("data-live-search", "true")
                            ->addAttribute("data-reference-table", $fk_table)
                            ->addAttribute("data-reference-column", $first_column_name);
                    } else {
                        /**
                         * Number input for integer field
                         */
                        $search_input = InputField::create($column_name)
                            ->setLabel($column_name)
                            ->setType("number");
                    }
                    if (isset($params[$column_name])) {
                        $query->condition("`{$column_name}` LIKE :{$column_name}", [":{$column_name}" => "%{$params[$column_name]}%"]);
                    }
                } elseif (in_array($column["Type"], ["datetime", "date"])) {
                    /**
                     * Adding daterange input for datetime and date fields
                     */
                    $search_input = InputField::create($column_name)
                        ->setLabel($column_name)
                        ->addClass("daterangeinput");
                    if (isset($params[$column_name])) {
                        $dates = explode("&", $params[$column_name]);
                        $query->condition(
                            "`{$column_name}` >= :{$column_name}_start AND `{$column_name}` <= :{$column_name}_end",
                            [
                                ":{$column_name}_start" => $dates[0]." 00:00:00",
                                ":{$column_name}_end" => $dates[1]." 23:59:59"
                            ]
                        );
                    }
                } elseif (in_array($column["Type"], ["time"])) {
                    /**
                     * Adding time input for time field
                     */
                    $search_input = InputField::create($column_name)
                        ->setLabel($column_name)
                        ->addClass("timeinput");
                } else {
                    /**
                     * Text input for uncategorized or text fields
                     */
                    $search_input = InputField::create($column_name)
                        ->setLabel($column_name);
                    if (isset($params[$column_name])) {
                        $query->condition("`{$column_name}` LIKE :{$column_name}", [":{$column_name}" => "%{$params[$column_name]}%"]);
                    }
                }
                if (isset($params[$column_name])) {
                    $search_input->setValue(filter_var($params[$column_name], FILTER_SANITIZE_STRING));
                }
                $search_input->addAttribute("autocomplete", "off");
                $this->search_form->addField(
                    Group::create("col-sm-3")->addField($search_input)
                );
            }
            /**
             * Adding search and reset buttons
             */
            $this->search_form->addField(
                Group::create("col-sm-12 d-flex mt-2")
                    ->addField(
                        InputField::create("search")
                            ->setType("submit")
                            ->setValue(_t("search"))
                            ->addClass("btn btn-primary mr-sm-1")
                    )->addField(
                        InputField::create("search")
                            ->setType("reset")
                            ->setValue(_t("reset"))
                            ->addClass("btn btn-danger ml-sm-1")
                    )
            );

            if (isset($_GET["orderby"]) && in_array($_GET["orderby"], $this->table_headers)) {
                $order_direction = isset($_GET["orderdirection"]) && $_GET["orderdirection"] == "DESC" ? "DESC" : "ASC";
                $query->orderBy("`{$_GET["orderby"]}` {$order_direction}");
            }
            $this->page = isset($params["page"]) && $params["page"] > 0 ? $params["page"] : 1;
            $this->record_count = $query
                ->select_with_function(["COUNT(*) AS count"])
                ->execute()->fetchObject()->count;

            $this->table_data = $query->unset_fields()
                ->select("", ["1 AS edit_buttons", "{$this->table_name}.*"])
                ->limit(PAGE_SIZE_LIMIT, ($this->page - 1) * PAGE_SIZE_LIMIT)
                ->execute()->fetchAll(PDO::FETCH_OBJ);
            foreach ($this->table_data as $row) {
                $row->edit_buttons = Group::create("d-flex")
                    ->addField(
                        TextElement::create(
                            "<a href='#' class='mr-2 rowdelete' data-table='{$this->table_name}' data-id='{$row->ID}'> 
                                <i class='fa fa-times text-danger core-control'></i>
                            </a>"
                        )
                    )->addField(
                        TextElement::create(
                            "<a href='" . BASE_URL . "/admin/table/insert/{$this->table_name}/{$row->ID}' class='ml-2'> 
                                <i class='fa fa-edit text-primary core-control row'></i>
                            </a>"
                        )
                    );
            }
        }

        $this->add_js_files("pages/admin/table/table.js");
    }

    protected function echoContent()
    {
        $content_group = Group::create("col-md-8")->addClass("order-md-first order-last")
            ->addField($this);
        if ($this->table_data !== null) {
            $content_group
                ->addField(
                    CollapsableCard::create(_t("search"))
                        ->setContent($this->search_form)
                        ->setId("search_form")
                )->addField(
                    Pagination::create($this->page)
                        ->setTotalCount($this->record_count)
                )
                ->addField(
                    (new Table($this->table_headers, $this->table_data))
                        ->setOrderable(true)
                )->addField(
                    Pagination::create($this->page)
                        ->setTotalCount($this->record_count)
                );
        }
        $group = new Group("container-fluid");
        $group->addField(
            Group::create("d-sm-flex align-items-center justify-content-between mb-4")
                ->addField(
                    Group::create("h3 mb-0 text-gray-800")->setTagName("h1")
                        ->addField(TextElement::create($this->title))
                )->addField(
                    Group::create("text-gray-600 ml-3 mr-auto")
                        ->addField(TextElement::create(_t($this->table_comment)))
                )->addField(
                    Group::create("d-sm-inline-block btn btn-sm btn-primary shadow-sm")
                        ->setTagName("a")->addAttribute("href", BASE_URL . "/admin/table/edit")->addField(
                            TextElement::create("<i class='fas fa-plus text-white-50'></i> " . _t("new_table"))
                        )
                )
        )->addField(
            Group::create("row")
                ->addField(
                    $content_group
                )->addField(SideBarTableList::create($this->table_name))
        );
        echo $group;
    }

    protected function add_default_translations()
    {
        parent::add_default_translations();
        $this->add_frontend_translation("truncate_accept");
        $this->add_frontend_translation("drop_accept");
        $this->add_frontend_translation("record_remove_accept");
    }
}
