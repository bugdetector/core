<?php

namespace Src\Form;

use Src\Entity\TreeEntityAbstract;
use CoreDB;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Messenger;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Form\Form;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Views\CollapsableCard;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class TreeForm extends Form
{

    public string $className;
    public string $method = "POST";
    public array $cards = [];
    public CollapsableCard $template;
    public $treeFieldName;
    public $newNodeAddUrl;
    public bool $showEditUrl = false;
    /**
     * A class name which extends TreeEntityAbstract.
     * @var $className
     */
    public function __construct(string $className, $newNodeAddUrl = null)
    {
        parent::__construct();
        $this->className = $className;
        $this->treeFieldName = $className::getTreeFieldName();
        $this->newNodeAddUrl = $newNodeAddUrl;
    }

    public function setShowEditUrl(bool $show)
    {
        $this->showEditUrl = $show;
    }
    public function getFormId(): string
    {
        return "tree_form";
    }

    public function getTemplateFile(): string
    {
        return "tree-form.twig";
    }

    public function processForm()
    {
        parent::processForm();
        $controller = CoreDB::controller();
        $controller->addJsFiles("dist/tree_form/tree_form.js");
        $controller->addFrontendTranslation("node_remove_warning");
        $className = $this->className;
        $this->addField(
            InputWidget::create("save")
            ->setType("submit")
            ->setValue(Translation::getTranslation("save"))
            ->addClass("btn btn-success")
        );
        $elements = $className::getRootElements();
        /** @var TreeEntityAbstract $element */
        foreach ($elements as $element) {
            $this->cards[] = $this->getTemplateCard($element);
        }
        $this->template = $this->getTemplateCard(new $className());
    }

    public function validate(): bool
    {
        return true;
    }

    public function submit()
    {
        $weight = 0;
        $className = $this->className;
        foreach ($this->request["tree"] as $nodeId => $nodeMap) {
            if (is_numeric($nodeId)) {
                $node = $className::get($nodeId);
            } else {
                $node = new $className();
            }
            $nodeMap["weight"] = $weight++;
            $node->map($nodeMap);
            $node->save();
        }
        $this->setMessage(
            Translation::getTranslation("change_success"),
            Messenger::SUCCESS
        );
        CoreDB::goTo(BASE_URL . CoreDB::requestUrl());
    }

    protected function getTemplateCard(TreeEntityAbstract $element): CollapsableCard
    {
        $nodeId = $element->ID->getValue();
        $treeFieldName = $element->getTreeFieldName();
        $cardTitle = "";
        if ($nodeId) {
            if (!($element->$treeFieldName instanceof TableReference)) {
                $cardTitle = $element->$treeFieldName->getValue();
            } else {
                $referenceObject = DynamicModel::get(
                    $element->$treeFieldName->getValue(),
                    $element->$treeFieldName->reference_table
                );
                $objectArray = $referenceObject->toArray();
                $cardTitle = current($objectArray);
            }
        } else {
            $cardTitle = Translation::getTranslation("new_node");
        }
        $card = CollapsableCard::create($cardTitle);
        $card->setId($nodeId ? "tree_{$element->ID->getValue()}" : "tree_template")
        ->setOpened(!boolval($nodeId))
        ->setSortable(true)
        ->setContent(
            $this->getTemplateCardContent($element)
        )->addClass("node-card")
        ->addAttribute("data-parent", $nodeId);
        return $card;
    }
    /**
     * Return a card content for template.
     */
    protected function getTemplateCardContent(TreeEntityAbstract $element)
    {
        $nodeId = $element->ID->getValue();
        $treeFieldName = $element->getTreeFieldName();
        /** @var FormWidget */
        $nameWidget = $element->$treeFieldName->getWidget()
        ->addClass("field");
        $nameWidget->setLabel(Translation::getTranslation("item_name"));
        if ($nodeId) {
            $nameWidget->setName("tree[{$nodeId}][$treeFieldName]");
        }
        $contentInputs = ViewGroup::create("div", "col-sm-6 col-md-4 col-lg-3")
        ->addField($nameWidget);
        if ($element->hasSubItems()) {
            $contentInputs->addField(
                InputWidget::create($nodeId ? "tree[{$nodeId}][parent]" : "")
                ->setValue($nodeId ? $element->parent->getValue() : "")
                ->setType("hidden")
                ->addClass("parent")
            );
        }
        $content = ViewGroup::create("div", "row")
        ->addField($contentInputs);
        
        if ($element->hasSubItems()) {
            $content->addField(
                $this->getSubCards($element)
            );
            if (!$this->newNodeAddUrl) {
                $content->addField(
                    Link::create(
                        "#",
                        TextElement::create(
                            '<i class="fa fa-plus"></i> ' . Translation::getTranslation("add_subitem")
                        )->setIsRaw(true)
                    )->addClass("btn btn-primary add-new-node")
                    ->addAttribute("data-parent", $nodeId)
                    ->addAttribute("data-field-name", $treeFieldName)
                );
            } else {
                $content->addField(
                    Link::create(
                        $this->newNodeAddUrl . "?parent={$nodeId}",
                        TextElement::create(
                            '<i class="fa fa-plus"></i> ' . Translation::getTranslation("add_subitem")
                        )->setIsRaw(true)
                    )->addClass("btn btn-primary")
                );
            }
        } else {
            $content->addField(
                ViewGroup::create("div", "w-100 mb-3")
            );
        }
        $content->addField(
            Link::create("#", TextElement::create(
                '<i class="fa fa-trash"></i> ' . Translation::getTranslation("delete")
            )->setIsRaw(true))->addClass("btn btn-danger ms-3 remove-node w-auto")
            ->addAttribute("data-node", $nodeId)
            ->addAttribute("data-service-url", $element->getRemoveServiceUrl())
        );
        if ($this->showEditUrl && $element->ID->getValue()) {
            $content->addField(
                Link::create(
                    $element->editUrl($nodeId),
                    TextElement::create(
                        '<i class="fa fa-edit"></i> ' . Translation::getTranslation("edit")
                    )->setIsRaw(true)
                )->addClass("btn btn-info ms-3 w-auto")
            );
        }
        return $content;
    }

    protected function getSubCards(TreeEntityAbstract $element = null): ViewGroup
    {
        $subCardGroup = ViewGroup::create("div", "sortable_list col-sm-12 mt-3");
        $subCardGroup->addAttribute("id", $element ? "parent-{$element->ID->getValue()}" : "");
        if ($element) {
            /** @var TreeEntityAbstract $subCard */
            foreach ($element->getSubNodes() as $subCard) {
                $subCardGroup->addField(
                    $this->getTemplateCard($subCard)
                );
            }
        }
        return $subCardGroup;
    }
}
