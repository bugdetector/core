<?php

namespace Src\Controller\Admin;

use CoreDB;
use Src\Controller\Admin\Entity\InsertController;
use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Form\TreeForm;

class NavbarController extends AdminController
{
    public ?TreeForm $treeForm;

    public function preprocessPage()
    {
        $this->setTitle(
            Translation::getTranslation("navbar")
        );
        $this->treeForm = new TreeForm(
            CoreDB::config()->getEntityClassName("navbar"),
            InsertController::getUrl() . "navbar"
        );
        $this->treeForm->setShowEditUrl(true);
        $this->treeForm->processForm();
    }

    public function echoContent()
    {
        return $this->treeForm;
    }
}
