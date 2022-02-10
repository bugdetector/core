<?php

namespace Src\Controller\Admin;

use CoreDB;
use Src\Controller\Admin\Entity\InsertController;
use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Form\TreeForm;

class SidebarController extends AdminController
{

    public ?TreeForm $treeForm;

    public function preprocessPage()
    {
        $this->setTitle(
            Translation::getTranslation("sidebar")
        );
        $this->treeForm = new TreeForm(
            CoreDB::config()->getEntityClassName("sidebar"),
            InsertController::getUrl() . "sidebar"
        );
        $this->treeForm->setShowEditUrl(true);
        $this->treeForm->processForm();
    }

    public function echoContent()
    {
        return $this->treeForm;
    }
}
