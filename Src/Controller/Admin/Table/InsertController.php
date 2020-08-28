<?php

namespace Src\Controller\Admin\Table;

use CoreDB\Kernel\Database\CoreDB;
use Src\Entity\DBObject;
use CoreDB\Kernel\Router;
use Src\Controller\Admin\TableController;
use Src\Entity\Translation;
use Src\Form\TableInsertForm;
use Src\Views\SideTableList;

class InsertController extends TableController
{

    const FORM_ID = "insert_form";

    public $object = null;
    public $insert_form;

    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        if (!$this->table_name) {
            Router::getInstance()->route(Router::NOT_FOUND);
        }
    }

    public function preprocessPage()
    {
        
        if (isset($this->arguments[1]) && !isset($_POST["insert?"])) {
            $this->object = DBObject::get(["ID" => $this->arguments[1]], $this->table_name);
            if (!$this->object) {
                Router::getInstance()->route(Router::NOT_FOUND);
            }
            $this->setTitle(Translation::getTranslation("edit") . " | " . $this->table_name . " ID: {$this->object->ID}");
        } elseif (!isset($_POST["delete?"])) {
            $this->object = new DBObject($this->table_name);
            $this->setTitle(Translation::getTranslation("add") . " | " . $this->table_name);
        } else {
            Router::getInstance()->route(Router::ACCESS_DENIED);
        }
        $this->insert_form = new TableInsertForm($this->object);
        $this->insert_form->processForm();
    }

    public function echoContent()
    {
        return $this->insert_form;
    }

    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("record_remove_accept");
    }
}
