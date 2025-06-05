<?php

namespace Src\Views;

use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\SelectWidget;

class TableAndColumnSelector extends CollapsableCard
{
    public const TYPE_COMPARISON = "comparison";
    public const TYPE_FIELD = "field";
    public const TYPE_ORDER = "order";
    public const TYPE_GROUP = "group";
    public $availableComparationTypes = [
        "=",
        "!=",
        ">",
        "<",
        ">=",
        "<=",
        "like",
        "not like",
        "in",
        "not in",
        "regexp",
        "not regexp",
    ];
    public $orderOptions = [
        "ASC" => "ascending",
        "DESC" => "descending"
    ];
    public $comparationTypes;

    public string $name;
    public string $type;
    protected string $subCardTitle;

    public function __construct($title, string $name, string $type)
    {
        parent::__construct($title);
        $this->name = $name;
        $this->type = $type;
        $this->setId($name);
        foreach ($this->availableComparationTypes as $comparation) {
            $this->comparationTypes[$comparation] = Translation::getTranslation($comparation);
        }
        $this->setOpened(true);
        if ($type == self::TYPE_COMPARISON) {
            $this->subCardTitle = Translation::getTranslation("new_filter");
            $this->addClass("filters");
        } else {
            $this->subCardTitle = Translation::getTranslation("new_field");
            $this->addClass("fields");
        }
        \CoreDB::controller()->addJsFiles("assets/js/views/table_and_column_selector.js");
    }

    public function setValue($value)
    {
        $values = json_decode(strval($value), true);
        if (!is_array($values) || empty($values)) {
            $values = [[]];
        }
        $this->content = ViewGroup::create("div", "sortable_list");
        if (is_array($values)) {
            foreach ($values as $index => $filter) {
                $title = $this->subCardTitle;
                if (array_key_exists("table", $filter) && array_key_exists("column", $filter)) {
                    $title = "{$filter["table"]}.{$filter["column"]}";
                    if (
                        $this->type == self::TYPE_COMPARISON &&
                        array_key_exists("comparation", $filter) &&
                        array_key_exists("compare_value", $filter)
                    ) {
                        $title .= " {$filter["comparation"]} {$filter["compare_value"]}";
                    }
                }
                $this->content->addField(
                    CollapsableCard::create($title)
                    ->setId("{$this->name}_{$index}")
                    ->setSortable(true)
                    ->setContent(
                        ViewGroup::create("div", "")
                        ->addField(
                            $this->getTableAndColumnSelectboxes($index, $filter)
                        )->addField(
                            ViewGroup::create("a", "btn btn-danger btn-sm removefilter mt-2")
                            ->addAttribute("href", "#")
                            ->addField(
                                ViewGroup::create("i", "fa fa-trash")
                            )
                            ->addField(TextElement::create(Translation::getTranslation("delete")))
                        )
                    )->addClass("table_and_column_selector")
                );
            }
        }
        $this->content->addField(
            ViewGroup::create("a", "btn btn-primary btn-sm mt-2 " .
            ($this->type == self::TYPE_COMPARISON ? "new_filter" : "new_field"))
            ->addAttribute("href", "#")
            ->addField(
                ViewGroup::create("i", "fa fa-add")
            )
            ->addField(TextElement::create(Translation::getTranslation("add")))
            ->addAttribute("data-name", $this->name)
            ->addAttribute("data-type", $this->type)
        );
    }

    private function getTableAndColumnSelectboxes($index, ?array $description = null): ViewGroup
    {
        $column_options = [];
        if ($this->type == self::TYPE_FIELD) {
            $column_options["*"] = Translation::getTranslation("all");
        }
        if (isset($description["table"]) && $description["table"]) {
            foreach (\CoreDB::database()->getTableDescription($description["table"]) as $fieldName => $field) {
                $column_options[$fieldName] = $fieldName;
            }
        }
        $widget = ViewGroup::create("div", "row")
        ->addField(
            ViewGroup::create("div", "col-sm-2")
            ->addField(
                SelectWidget::create("{$this->name}[{$index}][table]")
                ->setOptions(
                    \CoreDB::database()->getTableList()
                )
                ->setLabel(Translation::getTranslation("table_name"))
                ->setValue(isset($description["table"]) ? $description["table"] : "")
                ->addClass("table_select")
            )
        )
        ->addField(
            ViewGroup::create("div", "col-sm-2")
            ->addField(
                SelectWidget::create("{$this->name}[{$index}][column]")
                ->setOptions($column_options)
                ->setLabel(Translation::getTranslation("column_name"))
                ->setValue(isset($description["column"]) ? $description["column"] : "")
                ->addClass("column_select")
                ->addAttribute("data-type", $this->type)
            )
        );
        if ($this->type == self::TYPE_COMPARISON) {
            $widget->addField(
                ViewGroup::create("div", "col-sm-2")
                ->addField(
                    SelectWidget::create("{$this->name}[{$index}][comparation]")
                    ->setOptions($this->comparationTypes)
                    ->setLabel(Translation::getTranslation("comparation"))
                    ->setValue(isset($description["comparation"]) ? $description["comparation"] : "")
                )
            )->addField(
                ViewGroup::create("div", "col-sm-2")
                ->addField(
                    InputWidget::create("{$this->name}[{$index}][compare_value]")
                    ->setLabel(Translation::getTranslation("compare_value"))
                    ->setValue(isset($description["compare_value"]) ? $description["compare_value"] : "")
                )
            );
        } elseif ($this->type == self::TYPE_ORDER) {
            $widget->addField(
                ViewGroup::create("div", "col-sm-2")
                ->addField(
                    SelectWidget::create("{$this->name}[{$index}][orderdirection]")
                    ->setOptions($this->orderOptions)
                    ->setLabel(Translation::getTranslation("order_direction"))
                    ->setValue(isset($description["orderdirection"]) ? $description["orderdirection"] : "")
                )
            );
        }
        return $widget;
    }
}
