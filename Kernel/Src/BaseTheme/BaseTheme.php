<?php

namespace Src\BaseTheme;

use CoreDB\Kernel\BaseController;
use Src\Controller\LoginController;
use Src\Controller\LogoutController;
use Src\Controller\ProfileController;
use Src\Entity\Translation;
use Src\Views\Image;
use Src\Views\Navbar;
use Src\Views\NavItem;
use Src\Views\Sidebar;
use Src\Views\TextElement;

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
        return [__DIR__ . "/templates"];
    }

    public function processPage()
    {
        $this->buildNavbar();
        $this->buildSidebar();
        $this->addDefaultMetaTags();
        $this->addDefaultJsFiles();
        $this->addDefaultCssFiles();
        $this->addDefaultTranslations();
        $this->preprocessPage();
        $this->render();
    }
    
    public function buildNavbar()
    {
        $this->navbar = Navbar::create(
            "nav",
            "navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow"
        );
        $currentUser = \CoreDB::currentUser();
        /**   */
        $userDropdown = NavItem::create(
            Image::create($currentUser->getProfilePhotoUrl(), $currentUser->getFullName(), false)
            ->addClass("img-profile rounded-circle"),
            ""
        );
        $userDropdown->addClass("ms-auto");
        if ($currentUser->isLoggedIn()) {
            $userDropdown->addDropdownItem(
                NavItem::create(
                    "fa fa-user",
                    $currentUser->getFullName(),
                    ProfileController::getUrl()
                )
            )->addDropdownItem(
                NavItem::create(
                    "fa fa-sign-out-alt",
                    Translation::getTranslation("logout"),
                    LogoutController::getUrl()
                )
            );
        } else {
            $userDropdown->addDropdownItem(
                NavItem::create(
                    "fa fa-sign-in-alt",
                    Translation::getTranslation("login"),
                    LoginController::getUrl()
                )
            );
        }
        $userDropdown->addDropdownItem(
            NavItem::create("", "", "")
            ->addClass("dropdown-divider")
        );
        $translateIcons = Translation::get(["key" => "language_icon"]);
        foreach (Translation::getAvailableLanguageList() as $language) {
            $userDropdown->addDropdownItem(
                NavItem::create(
                    TextElement::create($translateIcons->$language->getValue())
                    ->setTagName("div")
                    ->setIsRaw(true)
                    ->addClass("d-inline-block"),
                    Translation::getTranslation($language),
                    "?lang={$language}"
                )
            );
        }
        $this->navbar->addNavItem(
            $userDropdown
        );
    }

    public function buildSidebar()
    {
        $this->sidebar = Sidebar::create();
    }

    public function echoContent()
    {
    }

    protected function addDefaultMetaTags()
    {
        $this->addMetaTag("charset", [
            "charset" => "utf-8"
        ]);
        $this->addMetaTag("viewport", [
            "name" => "viewport",
            "content" => "width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes"
        ]);
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
