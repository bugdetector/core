<?php

namespace Src\Controller\Admin\Table;

use CoreDB\Kernel\Messenger;
use Src\Controller\Admin\TableController;
use Src\Entity\Translation;
use Src\Form\TableStructForm;

class StructController extends TableController
{

    public TableStructForm $form;

    public function preprocessPage()
    {
        parent::preprocessPage();
        $this->form = new TableStructForm(strval($this->table_name), strval($this->table_comment));
        $this->form->processForm();
        if ($this->table_name) {
            $this->setTitle(Translation::getTranslation("edit_table") . " | {$this->table_name}");
        } elseif (!isset($this->arguments[0]) || !$this->arguments[0]) {
            $this->setTitle(Translation::getTranslation("new_table"));
        } else {
            \CoreDB::goTo($this->getUrl());
        }
    }

    public function echoContent()
    {
        return $this->form;
    }

    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("length");
        $this->addFrontendTranslation("reference_table");
        $this->addFrontendTranslation("field_drop_accept");
        $this->addFrontendTranslation("check_wrong_fields");
    }
}
