<?php

namespace Src\BaseTheme;

use CoreDB\Kernel\ControllerInterface;
use Src\Controller\LoginController;
use Src\Controller\LogoutController;
use Src\Controller\ProfileController;
use Src\Entity\Translation;
use Src\Theme\CoreRenderer;
use Src\Theme\ThemeInteface;
use Src\Views\Image;
use Src\Views\Navbar;
use Src\Views\NavItem;
use Src\Views\Sidebar;
use Src\Views\TextElement;

class BaseTheme implements ThemeInteface
{

    public Navbar $navbar;
    public Sidebar $sidebar;
    public $body_classes = [];

    public static function getTemplateDirectories(): array
    {
        return [__DIR__ . "/templates"];
    }

    public function render(ControllerInterface $controller)
    {
        $this->buildNavbar();
        $this->buildSidebar();
        $this->addDefaultMetaTags($controller);
        $this->addDefaultJsFiles($controller);
        $this->addDefaultCssFiles($controller);
        $this->addDefaultTranslations($controller);
        echo CoreRenderer::getInstance(
            $this::getTemplateDirectories()
        )->renderController($this, $controller);
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

    protected function addDefaultMetaTags(ControllerInterface $controller)
    {
        $controller->addMetaTag("charset", [
            "charset" => "utf-8"
        ]);
        $controller->addMetaTag("viewport", [
            "name" => "viewport",
            "content" => "width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes"
        ]);
    }
    
    protected function addDefaultJsFiles(ControllerInterface $controller)
    {
        $controller->addJsFiles("dist/_global/_global.js");
    }
    
    protected function addDefaultCssFiles(ControllerInterface $controller)
    {
        $controller->addCssFiles([
            "dist/_global/_global.css",
            "dist/icons/icons.css"
        ]);
    }
    
    protected function addDefaultTranslations(ControllerInterface $controller)
    {
        $controller->addFrontendTranslation("yes");
        $controller->addFrontendTranslation("no");
        $controller->addFrontendTranslation("cancel");
        $controller->addFrontendTranslation("warning");
        $controller->addFrontendTranslation("error");
        $controller->addFrontendTranslation("info");
        $controller->addFrontendTranslation("ok");
    }
}
