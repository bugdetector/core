<?php

namespace Src\Form;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\Date;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\Time;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\SearchableInterface;
use CoreDB\Kernel\TableMapper;
use PDO;
use Src\Entity\DBObject;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Views\CollapsableCard;
use Src\Views\Pagination;
use Src\Views\Table;
use Src\Views\ViewGroup;

class SearchForm extends Form
{
    public SearchableInterface $object;
    public bool $translateLabels;
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


    public static function createByObject(SearchableInterface $object, $translateLabels = true){
        $search_form = new SearchForm();
        $search_form->table_headers = $object->getTableHeaders();
        $search_form->query = $object->getTableQuery();
        $search_form->object = $object;
        $search_form->translateLabels = $translateLabels;
        $search_form->processForm();
        return $search_form;
    }

    public static function createByTableName($table_name)
    {
        $search_form = self::createByObject(new DBObject($table_name), false);
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

        foreach ($this->table_headers as $column_name => $value) {
            if (isset($params[$column_name]) && $params[$column_name] !== "") {
                if (in_array(get_class($this->object->$column_name), [DateTime::class, Date::class, Time::class])) {
                    $dates = explode("&", $params[$column_name]);
                    $this->query->condition($column_name, $dates[0] . " 00:00:00", ">=")
                    ->condition($column_name, $dates[1] . " 23:59:59", "<=");
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
        $queryResult = $this->query->execute();
        $this->table_data = $queryResult->fetchAll(PDO::FETCH_ASSOC);

        $search_input_group = new ViewGroup("div", "row");
        
        /**
         * @var DataTypeAbstract $dataType
         */
        foreach ($this->object->getSearchFormFields($this->translateLabels) as $field_name => $searchWidget) {
            $label = $this->translateLabels ? Translation::getTranslation($field_name) : $field_name;
            $search_input_group->addField(
                ViewGroup::create("div", "col-sm-3")->addField(
                    $searchWidget
                    ->setLabel($label)
                    ->setValue(isset($this->request[$field_name]) ? strval($this->request[$field_name]) : "")
                    ->setName($field_name)
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

        $this->search_input_group->setContent($search_input_group);
        
        foreach($this->table_data as &$row){
            $this->object->postProcessRow($row);
        }
        $this->pagination->total_count = $this->query->limit(0)->execute()->rowCount();
        $this->summary_text = Translation::getTranslation("table_summary", [
            $this->pagination->total_count,
            ($this->pagination->page -1) * 100 + 1,
            ($this->pagination->page) * 100 > $this->pagination->total_count ? $this->pagination->total_count : ($this->pagination->page) * 100
        ]);
        $this->table = new Table($this->table_headers, $this->table_data);
        $this->table->setOrderable(true);
    }
}
