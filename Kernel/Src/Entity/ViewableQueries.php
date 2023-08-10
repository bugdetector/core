<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\FilterableInterface;
use InvalidArgumentException;
use PDO;
use Src\Theme\ResultsViewer;
use Src\Views\TableAndColumnSelector;
use Src\Theme\View;
use Src\Views\Table;

/**
 * Object relation with table viewable_queries
 * @author murat
 */

class ViewableQueries extends Model implements FilterableInterface
{
    /**
     * Auto generated query will used.
     */
    public const EXECUTE_TYPE_DESCRIBED = "described";
    /**
     * Raw database query supplied.
     */
    public const EXECUTE_TYPE_RAW = "raw";
    /**
     * Table template will used.
     */
    public const RESULT_TEMPLATE_TABLE = "table";
    /**
     * A card will used for displaying results.
     */
    public const RESULT_TEMPLATE_CUSTOM_CARD = "custom_card";

    /**
     * @var ShortText $title
     * Administration title.
     */
    public ShortText $title;
    /**
     * @var Text $description
     * Administration description.
     */
    public Text $description;
    /**
     * @var ShortText $key
     * Programming key.
     */
    public ShortText $key;
    /**
     * @var Text $filters
     * Provided conditions data.
     */
    public Text $filters;
    /**
     * @var Text $result_fields
     * Required fields data.
     */
    public Text $result_fields;
    /**
     * @var Integer $paging_limit
     * Paginate results with limit. Default: 100.
     */
    public Integer $paging_limit;
    /**
     * @var Text $order_by
     * Order by and order direction.
     */
    public Text $order_by;
    /**
     * @var EnumaratedList $result_view_template
     * Use a template to show results.
     */
    public EnumaratedList $result_view_template;
    /**
     * @var ShortText $card_template_class
     * Enter a view name if you selected result template card.
     */
    public ShortText $card_template_class;
    /**
    * @var Checkbox $load_async
    * Load data async.
    */
    public Checkbox $load_async;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "viewable_queries";
    }

    public static function getInstance()
    {
        return new static();
    }
    /**
     * Returns an object by provided key.
     * @return ViewableQueries
     *  Viewable Query object.
     */
    public static function getByKey(string $key): ViewableQueries
    {
        return self::get(["key" => $key]);
    }

    /**
     * @inheritdoc
     */
    public function getResultHeaders(bool $translateLabel = true): array
    {
        if (!$this->ID->getValue()) {
            $parent_headers = parent::getResultHeaders($translateLabel);
            $headers = [
                0 => "",
                "title" => $parent_headers["title"],
                "description" => $parent_headers["description"],
                "created_at" => $parent_headers["created_at"],
                "last_updated" => $parent_headers["last_updated"]
            ];
        } else {
            $headers = [];
            foreach (json_decode($this->result_fields->getValue(), true) as $field) {
                $tableDefinition = TableDefinition::getDefinition($field["table"]);
                if ($field["column"] != "*") {
                    $headers[$field["column"]] = Translation::getTranslation($field["column"]);
                } else {
                    foreach ($tableDefinition->fields as $fieldName => $tableField) {
                        $headers[$fieldName] = Translation::getTranslation($fieldName);
                    }
                }
            }
        }
        return $headers;
    }
    /**
     * @inheritdoc
     */
    public function getSearchFormFields(bool $translateLabel = true): array
    {
        if (!$this->ID->getValue()) {
            $parent_fields = parent::getSearchFormFields($translateLabel);
            $fields = [
                "title" => $parent_fields["title"],
                "description" => $parent_fields["description"],
                "created_at" => $parent_fields["created_at"],
                "last_updated" => $parent_fields["last_updated"]
            ];
        } else {
            $fields = [];
            foreach (json_decode($this->result_fields->getValue(), true) as $field) {
                $tableDefinition = TableDefinition::getDefinition($field["table"]);
                if ($field["column"] != "*") {
                    $searchWidget = $tableDefinition->fields[$field["column"]]
                    ->getSearchWidget();
                    if ($searchWidget) {
                        $fields[$field["column"]] = $searchWidget->setName($field["column"])
                        ->setLabel(Translation::getTranslation($field["column"]));
                    }
                } else {
                    foreach ($tableDefinition->fields as $fieldName => $tableField) {
                        $fields[$fieldName] = $tableField->getSearchWidget()
                            ->setName($fieldName)
                            ->setLabel(Translation::getTranslation($fieldName));
                    }
                }
            }
        }
        return $fields;
    }

    public function getResultsViewer(): ResultsViewer
    {
        if ($this->ID->getValue() && $this->result_view_template->getValue() != self::RESULT_TEMPLATE_TABLE) {
            $cardClass = $this->card_template_class->getValue();
            /** @var ResultsViewer */
            $viewer = new $cardClass();
        } else {
            /** @var ResultsViewer */
            $viewer = parent::getResultsViewer();
        }
        $viewer->setAsyncLoad(
            boolval($this->load_async->getValue())
        );
        return $viewer;
    }

    /**
     * @inheritdoc
     */
    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        if (!$this->ID->getValue()) {
            return \CoreDB::database()->select($this->getTableName(), "vq")
                ->select("vq", [
                    "ID AS edit_actions",
                    "title",
                    "description",
                    "created_at",
                    "last_updated"
                ]);
        } else {
            return $this->parseQuery();
        }
    }

    public function getPaginationLimit(): int
    {
        if (!$this->ID->getValue()) {
            return parent::getPaginationLimit();
        } else {
            return $this->paging_limit->getValue();
        }
    }

    public function map(array $array, bool $isConstructor = false)
    {
        parent::map($array);
        if ($isConstructor) {
            return;
        }
        if (isset($this->changed_fields["filters"]) && is_array($this->changed_fields["filters"]["new_value"])) {
            $filters = [];
            foreach ($this->changed_fields["filters"]["new_value"] as $filter) {
                if (array_filter($filter)) {
                    $filters[] = $filter;
                }
            }
            $this->filters->setValue($filters);
        } else {
            $this->filters->setValue("");
        }
        if (
            isset($this->changed_fields["result_fields"]) &&
            !is_array($this->changed_fields["result_fields"]["new_value"])
        ) {
            throw new InvalidArgumentException(
                Translation::getTranslation("cannot_empty", [
                    Translation::getTranslation("result_fields")
                ])
            );
        } else {
            $this->result_fields->setValue(array_values(
                json_decode($this->result_fields->getValue(), true)
            ));
        }
        if (
            isset($this->changed_fields["paging_limit"]) &&
            (!$this->paging_limit->getValue() || $this->paging_limit->getValue() < 0)
        ) {
            $this->paging_limit->setValue(self::PAGE_LIMIT);
        }
        if (isset($this->changed_fields["card_template_class"])) {
            if (
                !$this->card_template_class->getValue() ||
                $this->result_view_template->getValue() == self::RESULT_TEMPLATE_TABLE
            ) {
                $this->card_template_class->setValue(Table::class);
            } elseif (!class_exists($this->card_template_class->getValue())) {
                throw new InvalidArgumentException(Translation::getTranslation("class_not_found"));
            }
        }
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "filters") {
            $widget = new TableAndColumnSelector(
                Translation::getTranslation("filters"),
                $this->getTableName() . "[filters]",
                TableAndColumnSelector::TYPE_COMPARISON
            );
            $widget->setValue($this->filters);
            return $widget;
        } elseif ($field_name == "result_fields") {
            $widget = new TableAndColumnSelector(
                Translation::getTranslation("result_fields"),
                $this->getTableName() . "[result_fields]",
                TableAndColumnSelector::TYPE_FIELD
            );
            $widget->setValue($this->result_fields);
            return $widget;
        } elseif ($field_name == "order_by") {
            $widget = new TableAndColumnSelector(
                Translation::getTranslation("order_by"),
                $this->getTableName() . "[order_by]",
                TableAndColumnSelector::TYPE_ORDER
            );
            $widget->setValue($this->order_by);
            return $widget;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }

    protected function parseQuery(): SelectQueryPreparerAbstract
    {
        $filters = $this->filters->getValue() ? json_decode($this->filters, true) : [];
        $fields = json_decode($this->result_fields, true);
        $usedTables = array_unique(
            array_map(function ($el) {
                return $el["table"];
            }, array_merge($filters, $fields))
        );
        $usedTables = array_values($usedTables);
        $query = \CoreDB::database()->select($usedTables[0]);
        $condition = \CoreDB::database()->condition($query);
        foreach ($filters as $filter) {
            $condition->condition(
                "{$filter["table"]}.{$filter["column"]}",
                $filter["compare_value"],
                $filter["comparation"]
            );
        }
        $query->condition($condition);
        foreach ($fields as $field) {
            $query->select($field["table"], [$field["column"]]);
        }
        $paths = $this->findAllJoins($usedTables, $usedTables[0]);
        foreach ($paths as $tableName => $joinInfo) {
            $query->join(
                $tableName,
                $tableName,
                "{$joinInfo["selfKey"]} = {$joinInfo["foreignKey"]}",
                "LEFT"
            );
        }
        if ($this->order_by->getValue()) {
            $orderByData = json_decode($this->order_by->getValue(), true);
            $order_by_sentence = "";
            foreach ($orderByData as $data) {
                $order_by_sentence .= ($order_by_sentence ? "," : "") .
                "{$data["table"]}.{$data["column"]} {$data["orderdirection"]}";
            }
            $query->orderBy($order_by_sentence);
        }
        return $query;
    }

    public function findAllJoins(array $tables, string $base_table)
    {
        $paths = [];
        foreach ($tables as $table) {
            if (
                $table == $base_table ||
                isset($joins[$base_table . "-" . $table]) ||
                isset($joins[$base_table . "-" . $table])
            ) {
                continue;
            }
            $path = $this->findJoinPath($base_table, $table);
            $paths = array_merge($paths, $path);
        }
        return $paths;
    }

    /**
     * tablo birleştirmesi için gerekli yolu bulur
     * @param string $start_table
     * @param string $end_table
     * @return array
     */
    public function findJoinPath(string $start_table, string $end_table): ?array
    {
        $queue = [$end_table];
        $visited = [$end_table];
        $graph = $this->getTableGraph();
        $graph[$end_table]["PARENT_NODE"] = -1;
        while (!empty($queue)) {
            $node = array_shift($queue);
            if ($node == $start_table) {
                $path = [];
                while ($graph[$node]["PARENT_NODE"] != -1) {
                    $field = "";
                    foreach ($graph[$node] as $field_name => $reference) {
                        if (is_array($reference) && $reference[0] == $graph[$node]["PARENT_NODE"]) {
                            $field = $graph[$node][$field_name][1];
                            break;
                        }
                    }
                    if ($field == "") {
                        foreach ($graph[$graph[$node]["PARENT_NODE"]] as $field_name => $reference) {
                            if ($reference[0] == $node) {
                                $field = $field_name;
                                break;
                            }
                        }
                    }
                    $path[$graph[$node]["PARENT_NODE"]] = [
                        "selfKey" => $node . "." . $graph[$node]["PARENT_COLUMN"],
                        "foreignKey" => $graph[$node]["PARENT_NODE"] . "." . $field
                    ];
                    $node = $graph[$node]["PARENT_NODE"];
                }
                return $path;
            }
            foreach ($graph[$node] as $neighbour) {
                if (is_array($neighbour) && !in_array($neighbour[0], $visited)) {
                    $visited[] = $neighbour[0];
                    $queue[] = $neighbour[0];
                    if (!isset($graph[$neighbour[0]]["PARENT_NODE"])) {
                        $graph[$neighbour[0]]["PARENT_NODE"] = $node;
                        $graph[$neighbour[0]]["PARENT_COLUMN"] = $neighbour[1];
                    }
                }
            }
        }
        return null;
    }

    protected function getTableGraph(): array
    {
        $cache = Cache::getByBundleAndKey("structure", "graph");
        if ($cache) {
            return json_decode($cache->value->getValue(), true);
        } else {
            $allReferences = \CoreDB::database()->getAllTableReferences()->fetchAll(PDO::FETCH_ASSOC);
            $graph = [];
            foreach ($allReferences as $reference) {
                $tableName = $reference["TABLE_NAME"];
                $columnName = $reference["COLUMN_NAME"];
                $referencedTableName = $reference["REFERENCED_TABLE_NAME"];
                $referencedColumnName = $reference["REFERENCED_COLUMN_NAME"];
                if (!isset($graph[$tableName])) {
                    $graph[$tableName] = [];
                }
                if (!isset($graph[$referencedTableName])) {
                    $graph[$referencedTableName] = [];
                }
                $graph[$tableName][$columnName] = [
                    $referencedTableName, $referencedColumnName
                ];
                $graph[$referencedTableName][] = [
                    $tableName, $columnName
                ];
            }
            Cache::set("structure", "graph", json_encode($graph));
            return $graph;
        }
    }
}
