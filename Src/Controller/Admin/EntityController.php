<?php
namespace Src\Controller\Admin;

use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\Messenger;

use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Form\SearchForm;
use Src\Views\SideEntityList;

class EntityController extends AdminController
{
    public $entityName;
    
    public SearchForm $search_form;
    public SideEntityList $side_entity_list;

    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        if (isset($this->arguments[0]) && $this->arguments[0] && in_array($this->arguments[0], \CoreDB::config()->getEntityList())) {
            $this->entityName = $this->arguments[0];
        }
    }

    public function processPage()
    {
        $this->side_entity_list = new SideEntityList($this->entityName);
        parent::processPage();
    }

    public function preprocessPage()
    {
        if (!$this->entityName) {
            $this->createMessage(Translation::getTranslation("table_select_info"), Messenger::INFO);
            $this->setTitle(Translation::getTranslation("entities"));
        } else {
            /**
             * Creating table and table search form
             */
            $this->setTitle(Translation::getTranslation("entities") . " | ".Translation::getTranslation($this->entityName));
            $className = \CoreDB::config()->getEntityInfo($this->entityName)["class"];
            $this->search_form = SearchForm::createByObject(new $className());
        }
    }

    public function getTemplateFile(): string
    {
        return "page-admin-entity.twig";
    }
}
