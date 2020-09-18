<?php

namespace Src\Theme\BaseTheme;

use CoreDB\Kernel\BaseController;
use Src\Controller\Admin\EntityController;
use Src\Controller\Admin\TableController;
use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Views\NavItem;
use Src\Views\Sidebar;

abstract class BaseTheme extends BaseController
{

    public Sidebar $sidebar;
    public $body_classes = [];

    public function checkAccess(): bool
    {
        return true;
    }

    public static function getTemplateDirectories(): array
    {
        return [__DIR__."/templates"];
    }

    public function processPage()
    {
        $this->buildSidebar();
        $this->addDefaultJsFiles();
        $this->addDefaultCssFiles();
        $this->addDefaultTranslations();
        $this->preprocessPage();
        $this->render();
    }

    public function buildSidebar()
    {
        $this->sidebar = Sidebar::create("ul", "navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled position-sticky");
        if (\CoreDB::currentUser()->isAdmin()) {
            $this->sidebar->addNavItem(
                NavItem::create(
                    "fa fa-tachometer-alt",
                    Translation::getTranslation("dashboard"),
                    BASE_URL. "/admin",
                    static::class == AdminController::class
                )
            )->addNavItem(
                NavItem::create(
                    "fa fa-cube",
                    Translation::getTranslation("entities"),
                    BASE_URL."/admin/entity",
                    $this instanceof EntityController
                )
            )->addNavItem(
                NavItem::create(
                    "fa fa-chart-area",
                    Translation::getTranslation("tables"),
                    BASE_URL."/admin/table",
                    $this instanceof TableController
                )
            )->addNavItem(
                NavItem::create(
                    "fa fa-broom",
                    Translation::getTranslation("clear_cache"),
                    "#"
                )->addClass("clear-cache")
            );
        }
    }

    public function echoContent()
    {
    }
    
    protected function addDefaultJsFiles()
    {
        $this->addJsFiles("dist/_global/_global.js");
    }
    
    protected function addDefaultCssFiles()
    {
        $this->addCssFiles([
            "dist/_global/_global.css",
            "dist/icons/icons.css"
        ]);
    }
    
    protected function addDefaultTranslations()
    {
        $this->addFrontendTranslation("yes");
        $this->addFrontendTranslation("no");
        $this->addFrontendTranslation("cancel");
        $this->addFrontendTranslation("warning");
        $this->addFrontendTranslation("error");
        $this->addFrontendTranslation("info");
        $this->addFrontendTranslation("ok");
    }
}
