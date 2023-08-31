<?php

namespace Src\Form;

use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\SearchableInterface;
use PDO;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Theme\CoreRenderer;
use Src\Theme\ResultsViewer;
use Src\Theme\View;
use Src\Views\NoResult;
use Src\Views\Pagination;
use Src\Views\Table;

class SearchForm extends Form
{
    protected $cacheKey;

    public SearchableInterface $object;
    public bool $translateLabels;
    public array $headers = [];
    public array $data = [];
    public Table $table;
    public array $searchInputs;
    public Pagination $pagination;
    public $page;
    public string $summary_text;
    public ?ResultsViewer $viewer;

    protected array $searchableFields = [];
    protected SelectQueryPreparerAbstract $query;

    protected function __construct(SearchableInterface $object, $translateLabels = true)
    {
        if ($this->method == "GET") {
            $this->request = $_GET;
        } elseif ($this->method == "POST") {
            $this->request = $_POST;
        }
        $this->object = $object;
        $this->headers = $object->getResultHeaders($translateLabels);
        $this->query = $object->getResultQuery();
        $this->translateLabels = $translateLabels;


        $page = intval(@$_GET["page"]);
        $this->page = $page >= 1 ? $page : 1;
        $this->pagination = new Pagination($this->page, $this->object->getPaginationLimit());
        $controller = \CoreDB::controller();
        $controller->addJsFiles("assets/js/forms/search_form.js");
        $controller->addFrontendTranslation("record_remove_accept");
        $controller->addFrontendTranslation("record_remove_accept_entity");
        /**
         * @var FormWidget $searchWidget
         */
        foreach ($this->object->getSearchFormFields($this->translateLabels) as $field_name => $searchWidget) {
            $searchFieldName = str_replace(".", "_", $field_name);
            $this->searchableFields[] = $field_name;
            $this->searchInputs[] = $searchWidget
                ->setValue(isset($this->request[$searchFieldName]) ? $this->request[$searchFieldName] : "")
                ->addAttribute("autocomplete", "off");
        }

        $this->addClass("search-form");
        $this->addAttribute("data-key", $this->getCacheKey());
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
        if (@$params["q"]) {
            $searchCondition = \CoreDB::database()->condition($this->query);
            $search = preg_replace(
                "/[^\w\s0-9a-zA-Z]/",
                "",
                mb_strtolower($params["q"])
            );
            foreach ($this->searchableFields as $column_name) {
                if (
                    !(@$this->object->$column_name instanceof EntityReference)
                ) {
                    $searchCondition->condition($column_name, "%" . $search . "%", "LIKE", "OR");
                }
            }
            $this->query->condition($searchCondition);
        }
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
        }
        $this->viewer = $this->object->getResultsViewer()
            ->setHeaders($this->headers)
            ->setData($this->data)
            ->setOrderable(true);
    }

    public function render()
    {
        $asynchToken = $this->getAsynchLoadToken();
        if ($this->viewer->useAsyncLoad) {
            \CoreDB::controller()->addJsCode("$(() => {
                $('.load-more-section').data('page', " . ($this->page + 1) . ")
            })");
        }
        \CoreDB::controller()->addJsCode("$(() => {
            $('.search-form[data-key=\'" . $this->getCacheKey() . "\']').data('token', '" . $asynchToken . "');
        })");
        parent::render();
    }

    protected function getCacheKey(): string
    {
        if (!$this->cacheKey) {
            $this->cacheKey = hash("sha256", json_encode($this->request) . Translation::getLanguage() . static::class);
        }
        return $this->cacheKey;
    }

    public function getAsynchLoadToken(): string
    {
        $tokenData = [
            "form" => static::class,
            "object" => serialize($this->object),
            "theme" => get_class(CoreRenderer::getInstance()->theme),
            "time" => time()
        ];
        $asyncLoadToken = hash("sha256", json_encode($tokenData));
        $_SESSION["search_asynch"][$asyncLoadToken] = $tokenData;
        return $asyncLoadToken;
    }

    public function noResultBehaviour(): View
    {
        return new NoResult(
            Translation::getTranslation("no_result_found")
        );
    }
}
