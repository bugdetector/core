<?php

namespace Src\Controller\Admin\Manage;

use Src\Controller\Admin\ManageController;
use Src\Entity\Translation;
use Src\Form\TableSearchForm;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class TranslationController extends ManageController
{

    public function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(Translation::getTranslation("translations"));
        $this->table_search_form = TableSearchForm::createByTableName(Translation::getTableName());
        $this->action_section = ViewGroup::create("div", "")->addField(
            ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm lang-imp")
                ->addAttribute("href", "#")
                ->addField(
                    ViewGroup::create("i", "fa fa-file-import text-white-50")
                )
                ->addField(
                    TextElement::create(Translation::getTranslation("import"))
                )
        )->addField(
            ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm ml-1 lang-exp")
                ->addAttribute("href", "#")
                ->addField(
                    ViewGroup::create("i", "fa fa-file-export text-white-50")
                )
                ->addField(
                    TextElement::create(Translation::getTranslation("export"))
                )
        );
        $this->addJsFiles("src/js/translation.js");
    }


    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("lang_import_info");
        $this->addFrontendTranslation("lang_export_info");
    }
}
