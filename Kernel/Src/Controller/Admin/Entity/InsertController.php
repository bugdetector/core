<?php

namespace Src\Controller\Admin\Entity;

use CoreDB\Kernel\Router;
use CoreDB\Kernel\Model;
use Src\Controller\Admin\EntityController;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\EntityTranslationForm;
use Src\Theme\View;

class InsertController extends EntityController
{
    public ?Model $object = null;
    public $insert_form;
    private View $translationContent;

    public function preprocessPage()
    {
        parent::preprocessPage();
        if (!$this->entityName) {
            Router::getInstance()->route(NotFoundController::getUrl());
        }
        if (isset($this->arguments[1])) {
            $this->object = $this->object->get(["ID" => $this->arguments[1]]);
            if (!$this->object) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $this->setTitle(Translation::getTranslation("edit") . " | " .
            Translation::getTranslation($this->entityName) . " ID: {$this->object->ID}");
        } else {
            $this->setTitle(Translation::getTranslation("add") . " | " .
            Translation::getTranslation($this->entityName));
        }
        $this->insert_form = $this->object->getForm();
        $this->translationContent = EntityTranslationForm::select($this->insert_form, $this->object);
    }

    public function echoContent()
    {
        return $this->translationContent ?? $this->insert_form;
    }
}
