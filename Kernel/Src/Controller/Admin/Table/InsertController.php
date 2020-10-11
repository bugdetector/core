<?php

namespace Src\Controller\Admin\Table;

use CoreDB\Kernel\Router;
use Src\Controller\Admin\TableController;
use Src\Controller\NotFoundController;
use Src\Entity\DBObject;
use Src\Entity\Translation;

class InsertController extends TableController
{
    public $object = null;
    public $insert_form;

    public function preprocessPage()
    {
        parent::preprocessPage();
        if (!$this->table_name) {
            Router::getInstance()->route(NotFoundController::getUrl());
        }
        if (isset($this->arguments[1])) {
            $this->object = DBObject::get($this->arguments[1], $this->table_name);
            if (!$this->object) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $this->setTitle(
                Translation::getTranslation("edit") . " | " .
                $this->table_name . " ID: {$this->object->ID}"
            );
        } else {
            $this->object = new DBObject($this->table_name);
            $this->setTitle(Translation::getTranslation("add") . " | " . $this->table_name);
        }
        $this->insert_form = $this->object->getForm();
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
