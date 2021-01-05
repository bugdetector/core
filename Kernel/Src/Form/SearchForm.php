<?php

namespace Src\Form;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\SearchableInterface;
use PDO;
use Src\Entity\DBObject;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Views\CollapsableCard;
use Src\Views\Pagination;
use Src\Views\Table;
use Src\Views\ViewGroup;

class SearchForm extends Form
{
    public SearchableInterface $object;
    public bool $translateLabels;
    public array $headers = [];
    public array $data = [];
    public Table $table;
    public CollapsableCard $search_input_group;
    public Pagination $pagination;
    public $page;
    public string $summary_text;
    
    private array $searchableFields = [];
    private SelectQueryPreparerAbstract $query;
    
    protected function __construct(SearchableInterface $object, $translateLabels = true)
    {
        parent::__construct();
        $this->object = $object;
        $this->headers = $object->getResultHeaders($translateLabels);
        $this->query = $object->getResultQuery();
        $this->translateLabels = $translateLabels;
        $this->search_input_group = new CollapsableCard(Translation::getTranslation("search"));
        $this->search_input_group->setId("search_input_group");
        $this->page = isset($_GET["page"]) ? $_GET["page"] : 1;
        $this->pagination = new Pagination($this->page, $this->object->getPaginationLimit());
        $controller = \CoreDB::controller();
        $controller->addJsFiles("dist/search_form/search_form.js");
        $controller->addFrontendTranslation("record_remove_accept");
        $controller->addFrontendTranslation("record_remove_accept_entity");

        $search_input_group = new ViewGroup("div", "row");
        
        /**
         * @var FormWidget $searchWidget
         */
        foreach ($this->object->getSearchFormFields($this->translateLabels) as $field_name => $searchWidget) {
            $this->searchableFields[] = $field_name;
            if (in_array("daterangeinput", $searchWidget->classes)) {
                $searchWidgetClass = "col-sm-6 col-lg-3";
            } else {
                $searchWidgetClass = "col-sm-3";
            }
            $search_input_group->addField(
                ViewGroup::create("div", $searchWidgetClass)->addField(
                    $searchWidget
                    ->setValue(isset($this->request[$field_name]) ? $this->request[$field_name] : "")
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
    }


    public static function createByObject(SearchableInterface $object, $translateLabels = true)
    {
        $search_form = new static($object, $translateLabels);
        $search_form->processForm();
        return $search_form;
    }

    public static function createByTableName($table_name)
    {
        $search_form = static::createByObject(new DBObject($table_name), false);
        return $search_form;
    }

    public function getFormId(): string
    {
        return "search_form";
    }
    public function getTemplateFile(): string
    {
        return "search_form.twig";
    }

    public function validate(): bool
    {
        return true;
    }

    public function submit()
    {
        $params = $this->request;
        $this->pagination->page = $this->page;
        $orderBy = isset($params["orderBy"]) &&
            in_array(
                $params["orderBy"],
                array_keys($this->headers)
            ) ? $params["orderBy"] : null;
        $orderDirection = isset($params["orderDirection"]) && $params["orderDirection"] == "DESC" ? "DESC" : "ASC";
        if ($orderBy) {
            $this->query->orderBy("`$orderBy` $orderDirection");
        }

        $condition = \CoreDB::database()->condition($this->query);
        foreach ($this->searchableFields as $column_name) {
            if (isset($params[$column_name]) && $params[$column_name] !== "") {
                if (
                    preg_match("/(\d{4}-\d{2}-\d{2}) & (\d{4}-\d{2}-\d{2})/", $params[$column_name])
                ) {
                    $dates = explode("&", $params[$column_name]);
                    $condition->condition($column_name, $dates[0] . " 00:00:00", ">=")
                    ->condition($column_name, $dates[1] . " 23:59:59", "<=");
                } elseif ($this->object->$column_name instanceof EntityReference) {
                    $condition->condition("{$column_name}.ID", $params[$column_name], "IN");
                } else {
                    $condition->condition($column_name, "%{$params[$column_name]}%", "LIKE");
                }
            }
        }
        $this->query->condition($condition);
    }

    public function processForm()
    {
        $this->submit();
        $this->query->limit($this->pagination->limit, ($this->pagination->page - 1) * $this->pagination->limit);
        $queryResult = $this->query->execute();
        $this->data = $queryResult->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($this->data as &$row) {
            $this->object->postProcessRow($row);
        }
        $this->pagination->total_count = $this->query->limit(0)->execute()->rowCount();
        $this->summary_text = Translation::getTranslation("result_summary", [
            $this->pagination->total_count,
            ($this->pagination->page - 1) * $this->pagination->limit + 1,
            ($this->pagination->page) * $this->pagination->limit > $this->pagination->total_count ?
            $this->pagination->total_count : ($this->pagination->page) * $this->pagination->limit
        ]);
        $this->viewer = $this->object->getResultsViewer()
        ->setHeaders($this->headers)
        ->setData($this->data)
        ->setOrderable(true);
    }
}
