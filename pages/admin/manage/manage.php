<?php

/**
 * @property FormBuilder $filter_options 
 */
class AdminManageController extends AdminController
{
    protected $page;
    protected $table_headers = [];
    protected $table_content = [];
    protected $filter_options;
    protected $action_section;
    protected $total_count;

    protected function preprocessPage()
    {
        $this->setTitle(_t("management"));
        $this->page = isset($_GET["page"]) && $_GET["page"] > 1 ? $_GET["page"] : 1;
    }

    protected function echoContent()
    {
        $table = new Table($this->table_headers, $this->table_content);
        $table->setOrderable(true);
        if (!$this->action_section) {
            $this->action_section = TextElement::create("");
        }
        if (!$this->filter_options) {
            $search_form = TextElement::create("");
            $summary_text = "";
        } else {
            $this->filter_options->addField(
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
            $search_form = Group::create("col-12")->addField(
                CollapsableCard::create(_t("search"))
                    ->setContent($this->filter_options)
                    ->setId("search_form")
            );
            $end = $this->page * PAGE_SIZE_LIMIT <= $this->total_count ? $this->page * PAGE_SIZE_LIMIT : $this->total_count; 
            $summary_text = _t("table_summary", [$this->total_count, ($this->page - 1) * PAGE_SIZE_LIMIT, $end]);
        }

        $nav_items = new NavPills();
        $nav_items->addNavItem(_t("user_management"), BASE_URL . "/admin/manage/user", get_called_class() == "AdminManageUserController")
            ->addNavItem(_t("role_management"), BASE_URL . "/admin/manage/role", get_called_class() == "AdminManageRoleController")
            ->addNavItem(_t("translations"), BASE_URL . "/admin/manage/translation", get_called_class() == "AdminManageTranslationController")
            ->addNavItem(_t("updates"), BASE_URL . "/admin/manage/update", get_called_class() == "AdminManageUpdateController");

        $group = new Group("container-fluid");
        $group->addField(
            Group::create("d-sm-flex align-items-center justify-content-between mb-4")
                ->addField(
                    Group::create("h3 mb-0 text-gray-800")->setTagName("h1")
                        ->addField(TextElement::create($this->title))
                )->addField(
                    $this->action_section
                )
        )->addField(
            $this
        )->addField(
            $search_form
        )->addField(
            Group::create("card shadow mb-4")->addField(
                Group::create("card-header py-3")
                    ->addField($nav_items)
                    ->addField(
                        Group::create("float-right")->addField(
                            TextElement::create($summary_text)
                        )
                    )
            )->addField(
                Group::create("col-12")->addField(
                    Pagination::create($this->page)
                        ->setTotalCount($this->total_count)
                )->addField(
                    $table
                )
            )->addField(
                Pagination::create($this->page)
                    ->setTotalCount($this->total_count)
            )
        );
        echo $group;
    }
}
