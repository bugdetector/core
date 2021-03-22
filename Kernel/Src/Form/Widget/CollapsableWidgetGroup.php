<?php

namespace Src\Form\Widget;

use CoreDB;
use CoreDB\Kernel\Model;
use Src\Entity\Translation;
use Src\Views\CollapsableCard;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class CollapsableWidgetGroup extends FormWidget
{

    public ViewGroup $fieldGroup;
    public string $newFieldName;
    public string $entityName;
    public array $hiddenFields = [];
    public bool $showAddButtonAndLabel = true;
    
    public function __construct(string $entityName, string $fieldEntityName)
    {
        parent::__construct("{$entityName}[{$fieldEntityName}]");
        $this->newFieldName = "{$entityName}[{$fieldEntityName}]";
        $this->entityName = $fieldEntityName;
        $this->fieldGroup = ViewGroup::create("div", "");
        $controller = CoreDB::controller();
        $controller->addJsFiles("dist/collapsible_widget_card/collapsible_widget_card.js");
        $this->addClass("collapsible-widget-group")
        ->removeClass("form-control");
    }

    public function setHiddenFields(array $hiddenFields)
    {
        $this->hiddenFields = $hiddenFields;
    }

    public static function create(string $entityName, string $fieldEntityName)
    {
        return new static($entityName, $fieldEntityName);
    }

    public function addCollapsibleObject(Model $object, int $index, bool $opened = false)
    {
        $this->fieldGroup->addField(
            self::getObjectCard(
                $object,
                $this->name,
                $index,
                $this->hiddenFields,
                $this->showAddButtonAndLabel
            )->setOpened($opened)
        );
    }

    public static function getObjectCard(
        Model $object,
        $name,
        $index,
        array $hiddenFields,
        bool $removeButton = true
    ): CollapsableCard {
        $content = ViewGroup::create("div", "");
        foreach ($object->getFormFields($name) as $fieldName => $field) {
            if (in_array($fieldName, $hiddenFields)) {
                continue;
            }
            $field->setName("{$name}[{$index}][{$fieldName}]");
            $content->addField($field);
        }
        if ($removeButton) {
            $content->addField(
                Link::create(
                    "#",
                    TextElement::create(
                        "<i class='fa fa-trash'></i> " . Translation::getTranslation("delete")
                    )->setIsRaw(true)
                )->addClass("btn btn-danger mt-2 remove-entity")
            );
        }
        return CollapsableCard::create(
            Translation::getTranslation($object->entityName) . " $index"
        )->setId(
            "{$name}_{$index}"
        )
        ->setContent($content);
    }

    public function getTemplateFile(): string
    {
        return "collapsible-widget-group.twig";
    }
}
