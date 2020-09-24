<?php

namespace Src\BaseTheme;

use CoreDB\Kernel\BaseController;
use Src\Controller\Admin\EntityController;
use Src\Controller\Admin\TableController;
use Src\Controller\AdminController;
use Src\Controller\LoginController;
use Src\Controller\LogoutController;
use Src\Entity\Translation;
use Src\Views\Navbar;
use Src\Views\NavItem;
use Src\Views\Sidebar;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

abstract class BaseTheme extends BaseController
{

    public Navbar $navbar;
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
        $this->buildNavbar();
        $this->buildSidebar();
        $this->addDefaultJsFiles();
        $this->addDefaultCssFiles();
        $this->addDefaultTranslations();
        $this->preprocessPage();
        $this->render();
    }
    
    public function buildNavbar(){
        $this->navbar = Navbar::create("nav", "navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow");
        $currentUser = \CoreDB::currentUser();
        $userDropdown = NavItem::create(
            ViewGroup::create("img", "img-profile rounded-circle")
            ->addAttribute("src", BASE_URL."/assets/default-profile-picture.png"),
            ""
        );
        if($currentUser->isLoggedIn()){
            $userDropdown->addDropdownItem(
                NavItem::create(
                    "fa fa-user",
                    Translation::getTranslation("profile"),
                    $currentUser->editUrl()
                )->setTagName("div")
            )->addDropdownItem(
                NavItem::create(
                    "fa fa-sign-out-alt",
                    Translation::getTranslation("logout"),
                    LogoutController::getUrl()
                )->setTagName("div")
            );
        }else{
            $userDropdown->addDropdownItem(
                NavItem::create(
                    "fa fa-sign-in-alt",
                    Translation::getTranslation("login"),
                    LoginController::getUrl()
                )->setTagName("div")
            );
        }
        $userDropdown->addDropdownItem(
            NavItem::create("", "", "")
            ->setTagName("div")
            ->addClass("dropdown-divider")
        );
        $translateIcons = Translation::get(["key" => "language_icon"]);
        foreach(Translation::getAvailableLanguageList() as $language){
            $userDropdown->addDropdownItem(
                NavItem::create(
                    TextElement::create($translateIcons->$language->getValue())
                    ->setTagName("div")
                    ->setIsRaw(true)
                    ->addClass("d-inline-block"), 
                    Translation::getTranslation($language), 
                    "?lang={$language}")
                ->setTagName("div")
            );
        }
        $this->navbar->addNavItem(
            $userDropdown
        );
    }

    public function buildSidebar()
    {
        $this->sidebar = Sidebar::create("ul", "navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled position-sticky");
        $currentUser = \CoreDB::currentUser();
        if ($currentUser->isAdmin()) {
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
