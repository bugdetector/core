<?php

namespace Src\Controller\Admin;

use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\TableMapper;
use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Form\SearchForm;
use Src\Views\SideEntityList;

class EntityController extends AdminController
{
    public $entityName;
    public ?TableMapper $object = null;
    public ?SearchForm $search_form = null;
    public SideEntityList $side_entity_list;
    public array $actions = [];

    public function preprocessPage()
    {
        if (
            isset($this->arguments[0]) && $this->arguments[0] &&
            in_array($this->arguments[0], \CoreDB::config()->getEntityList())
        ) {
            $this->entityName = $this->arguments[0];
            $className = \CoreDB::config()->getEntityInfo($this->entityName)["class"];
            $this->object = new $className();
            $this->actions = $this->object->actions();
            /**
             * Creating table and table search form
             */
            $this->setTitle(
                Translation::getTranslation("entities") . " | " .
                Translation::getTranslation($this->entityName)
            );
            $this->search_form = SearchForm::createByObject($this->object);
        } else {
            $this->createMessage(Translation::getTranslation("entity_select_info"), Messenger::INFO);
            $this->setTitle(Translation::getTranslation("entities"));
        }
        $this->side_entity_list = new SideEntityList($this->entityName);
    }

    public function echoContent()
    {
        return $this->search_form;
    }

    public function getTemplateFile(): string
    {
        return "page-admin-entity.twig";
    }
}
