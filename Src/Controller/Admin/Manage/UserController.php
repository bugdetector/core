<?php

namespace Src\Controller\Admin\Manage;

use Src\Controller\Admin\ManageController;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\TableSearchForm;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class UserController extends ManageController
{

    public function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(Translation::getTranslation("user_management"));
        $this->table_search_form = TableSearchForm::createByTableName(User::TABLE);
        $this->action_section = ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm add-role")
        ->addAttribute("href", BASE_URL."/admin/table/insert/".User::TABLE)
        ->addField(
            ViewGroup::create("i", "fa fa-plus text-white-50")
        )
        ->addField(
            TextElement::create(Translation::getTranslation("add_user"))
        );
    }


    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("remove_user_accept");
    }
}
