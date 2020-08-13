<?php
namespace Src\Controller\Admin\Manage;

use Src\Controller\Admin\ManageController;
use Src\Entity\Role;
use Src\Entity\Translation;
use Src\Form\TableSearchForm;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class RoleController extends ManageController{
    
    public function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(Translation::getTranslation("role_management"));
        $this->table_search_form = TableSearchForm::createByTableName(Role::TABLE);
        $this->action_section = ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm add-role")
        ->addAttribute("href", BASE_URL."/admin/table/insert/".Role::TABLE)
        ->addField(
            ViewGroup::create("i", "fa fa-plus text-white-50")
        )
        ->addField(
            TextElement::create(Translation::getTranslation("add_role"))
        );
    }
}