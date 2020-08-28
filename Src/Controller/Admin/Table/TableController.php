<?php
namespace Src\Controller\Admin;

use CoreDB\Kernel\Database\CoreDB;
use CoreDB\Kernel\Messenger;

use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Form\TableSearchForm;
use Src\Views\SideTableList;

class TableController extends AdminController
{
    public $table_name;
    public $table_comment;
    
    public TableSearchForm $table_search;
    public SideTableList $side_table_list;

    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        if (isset($this->arguments[0]) && $this->arguments[0] && in_array($this->arguments[0], \CoreDB::database()::getTableList())) {
            $this->table_name = $this->arguments[0];
            $this->table_comment = \CoreDB::database()::getTableComment($this->table_name);
        }
    }

    public function processPage()
    {
        $this->side_table_list = new SideTableList($this->table_name);
        parent::processPage();
    }

    public function preprocessPage()
    {
        if (!$this->table_name) {
            $this->createMessage(Translation::getTranslation("table_select_info"), Messenger::INFO);
            $this->setTitle(Translation::getTranslation("tables"));
        } else {
            /**
             * Creating table and table search form
             */
            $this->setTitle(Translation::getTranslation("tables") . " | {$this->table_name}");
            $this->table_search = TableSearchForm::createByTableName($this->table_name);
        }
    }

    public function getTemplateFile(): string
    {
        return "page-admin-table.twig";
    }

    protected function addDefaultJsFiles()
    {
        parent::addDefaultJsFiles();
        $this->addJsFiles("src/js/table.js");
    }

    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("truncate_accept");
        $this->addFrontendTranslation("drop_accept");
    }
}
