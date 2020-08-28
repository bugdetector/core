<?php

namespace Src\Controller\Admin;

use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Views\NavPills;

/**
 * @property FormBuilder $filter_options
 */
class ManageController extends AdminController
{
    public $table_search_form;
    public $action_section;
    public NavPills $nav_items;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("management"));
        $this->page = isset($_GET["page"]) && $_GET["page"] > 1 ? $_GET["page"] : 1;

        $this->nav_items = new NavPills();
        $this->nav_items->addNavItem(Translation::getTranslation("user_management"), BASE_URL . "/admin/manage/user", get_called_class() == "Src\Controller\Admin\Manage\UserController")
        ->addNavItem(Translation::getTranslation("role_management"), BASE_URL . "/admin/manage/role", get_called_class() == "Src\Controller\Admin\Manage\RoleController")
        ->addNavItem(Translation::getTranslation("translations"), BASE_URL . "/admin/manage/translation", get_called_class() == "Src\Controller\Admin\Manage\TranslationController");
    }

    public function getTemplateFile(): string
    {
        return "page-admin-manage.twig";
    }
}
