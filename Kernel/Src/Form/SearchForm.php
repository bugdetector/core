<?php

namespace Src\Form;

use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\SearchableInterface;
use PDO;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Theme\CoreRenderer;
use Src\Theme\ResultsViewer;
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
    public ?ResultsViewer $viewer;

    protected array $searchableFields = [];
    protected SelectQueryPreparerAbstract $query;

    protected function __construct(SearchableInterface $object, $translateLabels = true)
    {
        parent::__construct();
        $this->object = $object;
        $this->headers = $object->getResultHeaders($translateLabels);
        $this->query = $object->getResultQuery();
        $this->translateLabels = $translateLabels;
        $this->search_input_group = new CollapsableCard(Translation::getTranslation("search"));
        $this->search_input_group->setId("search_input_group");
        $page = intval(@$_GET["page"]);
        $this->page = $page >= 1 ? $page : 1;
        $this->pagination = new Pagination($this->page, $this->object->getPaginationLimit());
        $controller = \CoreDB::controller();
        $controller->addJsFiles("assets/js/forms/search_form.js");
        $controller->addFrontendTranslation("record_remove_accept");
        $controller->addFrontendTranslation("record_remove_accept_entity");

        $search_input_group = new ViewGroup("div", "row");

        /**
         * @var FormWidget $searchWidget
         */
        foreach ($this->object->getSearchFormFields($this->translateLabels) as $field_name => $searchWidget) {
            $searchFieldName = str_replace(".", "_", $field_name);
            $this->searchableFields[] = $field_name;
            if (in_array("daterangeinput", $searchWidget->classes)) {
                $searchWidgetClass = "col-sm-6 col-lg-3";
            } else {
                $searchWidgetClass = "col-sm-3";
            }
            $search_input_group->addField(
                ViewGroup::create("div", $searchWidgetClass)->addField(
                    $searchWidget
                    ->setValue(isset($this->request[$searchFieldName]) ? $this->request[$searchFieldName] : "")
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
                        ->addClass("btn btn-sm btn-primary me-sm-1")
                )->addField(
                    InputWidget::create("reset")
                        ->setType("reset")
                        ->setValue(Translation::getTranslation("reset"))
                        ->addClass("btn btn-sm btn-danger ms-sm-1")
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
        $search_form = static::createByObject(new DynamicModel($table_name), false);
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
            $paramField = str_replace(".", "_", $column_name);
            if (isset($params[$paramField]) && $params[$paramField] !== "") {
                if (
                    !is_array($params[$paramField]) &&
                    preg_match("/(\d{4}-\d{2}-\d{2}) & (\d{4}-\d{2}-\d{2})/", $params[$paramField])
                ) {
                    $dates = explode("&", $params[$paramField]);
                    $condition->condition($column_name, $dates[0] . " 00:00:00", ">=")
                    ->condition($column_name, $dates[1] . " 23:59:59", "<=");
                } elseif (@$this->object->$column_name instanceof EntityReference) {
                    $params[$paramField] = is_array($params[$paramField]) ? array_filter($params[$paramField]) : [];
                    if ($params[$paramField]) {
                        $condition->condition("{$column_name}.ID", $params[$paramField], "IN");
                    }
                } elseif (is_array($params[$paramField])) {
                    $condition->condition($column_name, $params[$paramField]);
                } else {
                    $condition->condition($column_name, "%{$params[$paramField]}%", "LIKE");
                }
            }
        }
        $this->query->condition($condition);
    }

    public function processForm()
    {
        if (!$this->getCache()) {
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

    public function render()
    {
        if ($this->viewer->useAsyncLoad) {
            \CoreDB::controller()->addJsCode("$(() => {
                $('.load-more-section').data('token', '" . $this->getAsynchLoadToken() . "')
                    .data('page', " . ($this->page + 1) . ")
            })");
        }
        parent::render();
    }

    protected function getCacheKey(): string
    {
        return hash("sha256", json_encode($this->request) . Translation::getLanguage() . static::class);
    }

    public function getAsynchLoadToken(): string
    {
        $tokenData = [
            "form" => static::class,
            "object" => serialize($this->object),
            "theme" => get_class(CoreRenderer::getInstance()->theme),
            "time" => time()
        ];
        $autoLoadToken = hash("sha256", json_encode($tokenData));
        $_SESSION["autoload"][$autoLoadToken] = $tokenData;
        return $autoLoadToken;
    }
}
