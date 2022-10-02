<?php

namespace Src\Traits\Controller;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\SearchForm;

trait ListFormControllerTrait
{
    public ?Model $model = null;
    public $modelForm;

    public function preprocessPage()
    {
        $modelClass = $this->getModelClass();
        if (isset($this->arguments[0])) {
            if ($this->arguments[0] == "add") {
                $this->model = new $modelClass();
                $this->setTitle(
                    $this->getAddTitle()
                );
            } else {
                $this->model = $modelClass::get($this->arguments[0]);
                if (!$this->model) {
                    Router::getInstance()->route(NotFoundController::getUrl());
                }
                $this->setTitle(
                    $this->getUpdateTitle($this->model)
                );
            }
            $this->modelForm = $this->model->getForm();
            $this->modelForm->processForm();
        } else {
            $this->model = new $modelClass();
            $this->setTitle(Translation::getTranslation($this->model->entityName));
            $this->modelForm = SearchForm::createByObject($this->model);
        }
        $this->actions = $this->model->actions();
        $this->modelForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->modelForm;
    }

    public function getTemplateFile(): string
    {
        return "page.twig";
    }

    abstract protected function getModelClass(): string;
    abstract protected function getAddTitle(): string;
    abstract protected function getUpdateTitle(Model $model): string;
}
