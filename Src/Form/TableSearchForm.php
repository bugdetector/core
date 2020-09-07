<?php

namespace Src\Form;

use CoreDB;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\Date;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\Time;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\TableMapper;
use PDO;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Views\CollapsableCard;
use Src\Views\Pagination;
use Src\Views\Table;
use Src\Views\ViewGroup;

class TableSearchForm extends Form
{
    public string $table_name;
    public array $table_headers = [];
    public array $table_data = [];
    public Table $table;
    public CollapsableCard $search_input_group;
    public Pagination $pagination;
    public string $summary_text;
    private SelectQueryPreparerAbstract $query;
    public function __construct()
    {
        parent::__construct();
        $this->search_input_group = new CollapsableCard(Translation::getTranslation("search"));
        $this->search_input_group->setId("search_input_group");
        $this->pagination = new Pagination(isset($_GET["page"]) ? $_GET["page"] : 1);
        \CoreDB::controller()->addJsFiles("src/js/table_search_form.js");
        \CoreDB::controller()->addFrontendTranslation("record_remove_accept");
    }

    public static function createByTableName($table_name)
    {

        $search_form = new TableSearchForm();
        $search_form->table_name = $table_name;
        $search_form->table_headers[] = "";
        $search_form->query = CoreDB::database()->select($table_name);
        $search_form->query->select($table_name, ["ID AS edit_actions", "*"]);
        
        $search_input_group = new ViewGroup("div", "row");

        /**
         * @var DataTypeAbstract $dataType
         */
        foreach (\CoreDB::database()::getTableDescription($table_name) as $dataType) {
            $search_form->table_headers[$dataType->column_name] = Translation::getTranslation($dataType->column_name);

            $params = array_filter($_GET);
            $search_input_group->addField(
                ViewGroup::create("div", "col-sm-3")->addField(
                    $dataType->getSearchWidget()
                    ->setLabel(Translation::getTranslation($dataType->column_name))
                    ->setValue(isset($params[$dataType->column_name]) ? strval($params[$dataType->column_name]) : "")
                    ->setName($dataType->column_name)
                    ->addAttribute("autocomplete", "off")
                )
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
        $params = $this->request;
        $this->pagination->page = isset($params["page"]) ? $params["page"] : 1;
        $orderBy = isset($params["orderBy"]) && in_array($params["orderBy"], array_keys($this->table_headers)) ? $params["orderBy"] : null;
        $orderDirection = isset($params["orderDirection"]) && $params["orderDirection"] == "DESC" ? "DESC" : "ASC";
        if ($orderBy) {
            $this->query->orderBy("`$orderBy` $orderDirection");
        }

        /**
         * @var DataTypeAbstract $dataType
         */
        foreach (\CoreDB::database()::getTableDescription($this->table_name) as $dataType) {
            $column_name = $dataType->column_name;
            if (isset($params[$column_name]) && $params[$column_name] !== "") {
                if (in_array(get_class($dataType), [DateTime::class, Date::class, Time::class])) {
                    $dates = explode("&", $params[$column_name]);
                    $this->query->condition($column_name, $dates[0] . " 00:00:00", ">=")
                    ->condition($column_name, $dates[0] . " 23:59:59", "<=");
                } else {
                    $this->query->condition($column_name, "%{$params[$column_name]}%", "LIKE");
                }
            }
        }
    }

    public function processForm()
    {
        $this->submit();
        $this->query->limit(100, ($this->pagination->page -1) * 100);
        $this->table_data = $this->query->execute()->fetchAll(PDO::FETCH_ASSOC);
        foreach($this->table_data as &$row){
            $row["edit_actions"] = ViewGroup::create("div", "d-flex")
            ->addField(
                ViewGroup::create("a", "mr-2 rowdelete")
                ->addField(
                    ViewGroup::create("i", "fa fa-times text-danger core-control")
                )
                ->addAttribute("data-table", $this->table_name)
                ->addAttribute("data-id", $row["ID"])
                ->addAttribute("href", "#")
            )->addField(
                ViewGroup::create("a", "ml-2")
                ->addField(
                    ViewGroup::create("i", "fa fa-edit text-primary core-control")
                )
                ->addAttribute("href", TableMapper::editUrl($this->table_name, $row["ID"]))
                );
        }
        $this->pagination->total_count = $this->query->limit(0)->execute()->rowCount();
        $this->summary_text = Translation::getTranslation("table_summary", [
            $this->pagination->total_count,
            ($this->pagination->page -1) * 100 + 1,
            ($this->pagination->page) * 100 > $this->pagination->total_count ? $this->pagination->total_count : ($this->pagination->page) * 100
        ]);
        $this->table = new Table($this->table_headers, $this->table_data);
        $this->table->table_name = $this->table_name;
        $this->table->setOrderable(true);
    }
}
