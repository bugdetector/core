<?php

namespace Src\Controller\Admin;

use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\Messenger;
use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Form\SearchForm;
use Src\Views\SideTableList;

class TableController extends AdminController
{
    public $table_name;
    public $table_comment;
    
    public ?SearchForm $table_search = null;
    public SideTableList $side_table_list;

    public function preprocessPage()
    {
        if (
            isset($this->arguments[0]) && $this->arguments[0] &&
            in_array($this->arguments[0], \CoreDB::database()::getTableList())
        ) {
            $table_definition = TableDefinition::getDefinition($this->arguments[0]);
            $this->table_name = $this->arguments[0];
            $this->table_comment = $table_definition->table_comment;
            $this->table_search = SearchForm::createByTableName($this->table_name);
            /**
             * Creating table and table search form
             */
            $this->setTitle(Translation::getTranslation("tables") . " | {$this->table_name}");
        } elseif (get_class($this) == TableController::class) {
            $this->createMessage(Translation::getTranslation("table_select_info"), Messenger::INFO);
            $this->setTitle(Translation::getTranslation("tables"));
        }
        $this->side_table_list = new SideTableList($this->table_name);
    }

    public function echoContent()
    {
        return $this->table_search;
    }

    public function getTemplateFile(): string
    {
        return "page-admin-table.twig";
    }

    protected function addDefaultJsFiles()
    {
        parent::addDefaultJsFiles();
    }

    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("truncate_accept");
        $this->addFrontendTranslation("drop_accept");
    }
}
