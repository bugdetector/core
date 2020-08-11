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
        $this->form = new TableStructForm(strval($this->table_name), strval($this->table_comment));
        $this->form->processForm();
        if ($this->table_name) {
            $this->setTitle(Translation::getTranslation("edit_table") . " | {$this->table_name}");
        } else if (!isset($this->arguments[0])) {
            $this->setTitle(Translation::getTranslation("new_table"));
        } else {
            \CoreDB::goTo(BASE_URL . "/admin/table/struct");
        }
    }

    public function echoContent()
    {
        return $this->form;
    }

    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("length_varchar");
        $this->addFrontendTranslation("reference_table");
        $this->addFrontendTranslation("field_drop_accept");
        $this->addFrontendTranslation("check_wrong_fields");
    }
}
