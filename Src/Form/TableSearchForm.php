<?php

namespace Src\Form;

use CoreDB\Kernel\Database\CoreDB;
use CoreDB\Kernel\Database\SelectQueryPreparer;

use PDO;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\SelectWidget;
use Src\Theme\Views\CollapsableCard;
use Src\Theme\Views\Pagination;
use Src\Theme\Views\Table;
use Src\Theme\Views\ViewGroup;

class TableSearchForm extends Form
{
    public string $table_name;
    public array $table_headers = [];
    public array $table_data = [];
    public Table $table;
    public CollapsableCard $search_input_group;
    public Pagination $pagination;
    private SelectQueryPreparer $query;
    public function __construct()
    {
        parent::__construct();
        $this->search_input_group = new CollapsableCard(Translation::getTranslation("search"));
        $this->search_input_group->setId("search_input_group");
        $this->pagination = new Pagination(isset($_GET["page"]) ? $_GET["page"] : 1);
        \CoreDB::controller()->addJsFiles("src/js/table.js");
        \CoreDB::controller()->addFrontendTranslation("record_remove_accept");
    }

    public static function createByTableName($table_name)
    {

        $search_form = new TableSearchForm();
        $search_form->table_name = $table_name;
        $search_form->table_headers[] = "";
        $search_form->query = new SelectQueryPreparer($table_name);
        $search_form->query->select($table_name, ["ID AS edit_actions", "*"]);
        
        $search_input_group = new ViewGroup("div", "row");
        foreach (\CoreDB::database()::getTableDescription($table_name) as $column) {
            $column_name = $column["Field"];
            $search_form->table_headers[$column_name] = Translation::getTranslation($column_name);

            if ($column["Type"] == "tinytext") {
                $file_fields[] = $column["Field"];
            }
            $search_input = null;

            $params = array_filter($_GET);
            if (in_array($column["Type"], ["int", "double"])) {
                if (\CoreDB::database()::isUniqueForeignKey($table_name, $column_name)) {
                    /**
                     * Creating referenced table select input for foreign keys
                     */
                    $fk_description = \CoreDB::database()::getForeignKeyDescription($table_name, $column_name);
                    $fk_table = $fk_description["REFERENCED_TABLE_NAME"];
                    $options = [];
                    $first_column_name = \CoreDB::database()::getTableDescription($fk_table)[1]["Field"];
                    $fk_query = new SelectQueryPreparer($fk_table);
                    if (isset($params[$column_name])) {
                        $fk_query->condition("ID = :id", [":id" => $params[$column_name]]);
                    } else {
                        $fk_query->limit(AUTOCOMPLETE_SELECT_BOX_LIMIT);
                    }
                    foreach ($fk_query->execute()->fetchAll(PDO::FETCH_OBJ) as $record) {
                        $options[$record->ID] = "{$record->ID} {$record->$first_column_name}";
                    }
                    $search_input = SelectWidget::create($column_name)
                        ->setLabel(Translation::getTranslation($column_name))
                        ->setOptions($options)
                        ->addClass("autocomplete")
                        ->addAttribute("data-live-search", "true")
                        ->addAttribute("data-reference-table", $fk_table)
                        ->addAttribute("data-reference-column", $first_column_name);
                } else {
                    /**
                     * Number input for integer field
                     */
                    $search_input = InputWidget::create($column_name)
                        ->setLabel(Translation::getTranslation($column_name))
                        ->setType("number");
                }
            } elseif (in_array($column["Type"], ["datetime", "date"])) {
                /**
                 * Adding daterange input for datetime and date fields
                 */
                $search_input = InputWidget::create($column_name)
                    ->setLabel(Translation::getTranslation($column_name))
                    ->addClass("daterangeinput");
            } elseif (in_array($column["Type"], ["time"])) {
                /**
                 * Adding time input for time field
                 */
                $search_input = InputWidget::create($column_name)
                    ->setLabel(Translation::getTranslation($column_name))
                    ->addClass("timeinput");
            } else {
                /**
                 * Text input for uncategorized or text fields
                 */
                $search_input = InputWidget::create($column_name)
                    ->setLabel(Translation::getTranslation($column_name));
            }
            if (isset($params[$column_name])) {
                $search_input->setValue(filter_var($params[$column_name], FILTER_SANITIZE_STRING));
            }
            $search_input->addAttribute("autocomplete", "off");
            if (isset($params[$column_name])) {
                $search_input->setValue($params[$column_name]);
            }
            $search_input_group->addField(
                ViewGroup::create("div", "col-sm-3")->addField($search_input)
            );
        }

        /**
         * Adding search and reset buttons
         */
        $search_input_group->addField(
            ViewGroup::create("div", "col-sm-12 d-flex mt-2")
                ->addField(
                    InputWidget::create("search")
                        ->setType("submit")
                        ->setValue(Translation::getTranslation("search"))
                        ->addClass("btn btn-primary mr-sm-1")
                )->addField(
                    InputWidget::create("search")
                        ->setType("reset")
                        ->setValue(Translation::getTranslation("reset"))
                        ->addClass("btn btn-danger ml-sm-1")
                )
        );

        $search_form->search_input_group->setContent($search_input_group);

        $search_form->processForm();
        return $search_form;
    }

    public function getFormId(): string
    {
        return "table_search_form";
    }
    public function getTemplateFile(): string
    {
        return "table_search_form.twig";
    }

    public function validate(): bool
    {
        return true;
    }

    public function submit()
    {
        $params = array_filter($_GET);
        $this->pagination->page = isset($params["page"]) ? $params["page"] : 1;
        $orderBy = isset($params["orderBy"]) && in_array($params["orderBy"], array_keys($this->table_headers)) ? $params["orderBy"] : null;
        $orderDirection = isset($params["orderDirection"]) && $params["orderDirection"] == "DESC" ? "DESC" : "ASC";
        if ($orderBy) {
            $this->query->orderBy("`$orderBy` $orderDirection");
        }

        foreach (\CoreDB::database()::getTableDescription($this->table_name) as $column) {
            $column_name = $column["Field"];
            if (isset($params[$column_name]) && $params[$column_name]) {
                if (in_array($column["Type"], ["datetime", "date"])) {
                    $dates = explode("&", $params[$column_name]);
                    $this->query->condition(
                        "`{$column_name}` >= :{$column_name}_start AND `{$column_name}` <= :{$column_name}_end",
                        [
                            ":{$column_name}_start" => $dates[0] . " 00:00:00",
                            ":{$column_name}_end" => $dates[1] . " 23:59:59"
                        ]
                    );
                } else {
                    $this->query->condition("`{$column_name}` LIKE :{$column_name}", [":{$column_name}" => "%{$params[$column_name]}%"]);
                }
            }
        }
    }

    public function processForm()
    {
        $this->submit();
        $this->query->limit(PAGE_SIZE_LIMIT, ($this->pagination->page -1) * PAGE_SIZE_LIMIT);
        $this->table_data = $this->query->execute()->fetchAll(PDO::FETCH_ASSOC);
        $this->pagination->total_count = $this->query->limit(0)->execute()->rowCount();
        $this->table = new Table($this->table_headers, $this->table_data);
        $this->table->table_name = $this->table_name;
        $this->table->setOrderable(true);
    }
}
