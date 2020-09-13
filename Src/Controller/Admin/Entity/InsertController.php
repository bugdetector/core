<?php

namespace Src\Controller\Admin\Entity;

use CoreDB\Kernel\Router;
use CoreDB\Kernel\TableMapper;
use Src\Controller\Admin\EntityController;
use Src\Entity\Translation;

class InsertController extends EntityController
{

    public ?TableMapper $object = null;
    public $insert_form;

    public function preprocessPage()
    {
        parent::preprocessPage();
        if (!$this->entityName) {
            Router::getInstance()->route(Router::NOT_FOUND);
        }
        if (isset($this->arguments[1]) && !isset($_POST["insert?"])) {
            $this->object = $this->object->get(["ID" => $this->arguments[1]]);
            if (!$this->object) {
                Router::getInstance()->route(Router::NOT_FOUND);
            }
            $this->setTitle(Translation::getTranslation("edit") . " | " . Translation::getTranslation($this->entityName) . " ID: {$this->object->ID}");
        } elseif (!isset($_POST["delete?"])) {
            $this->setTitle(Translation::getTranslation("add") . " | " . Translation::getTranslation($this->entityName));
        } else {
            Router::getInstance()->route(Router::ACCESS_DENIED);
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
