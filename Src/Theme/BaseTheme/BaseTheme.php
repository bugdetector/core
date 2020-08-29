<?php

namespace Src\Theme\BaseTheme;

use CoreDB\Kernel\BaseController;
use Src\Entity\Translation;
use Src\Views\NavItem;
use Src\Views\Sidebar;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

abstract class BaseTheme extends BaseController
{

    public Sidebar $sidebar;
    public string $title = "";
    public $body_classes = [];

    public function checkAccess(): bool
    {
        return true;
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    public static function getTemplateDirectories(): array
    {
        return [__DIR__."/templates"];
    }

    public function getTemplateFile() : string
    {
        return "page.twig";
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
                    BASE_URL. "/admin"
                )
            )->addNavItem(
                NavItem::create(
                    "fa fa-cog",
                    Translation::getTranslation("management"),
                    "#"
                )
                        ->addCollapsedItem(
                            TextElement::create(Translation::getTranslation("management"))
                            ->setTagName("h6"),
                            true
                        )->addCollapsedItem(
                            TextElement::create(Translation::getTranslation("user_management"))
                            ->setTagName("a")
                            ->addAttribute("href", BASE_URL."/admin/manage/user")
                        )
                        ->addCollapsedItem(
                            TextElement::create(Translation::getTranslation("role_management"))
                            ->setTagName("a")
                            ->addAttribute("href", BASE_URL."/admin/manage/role")
                        )
                        ->addCollapsedItem(
                            TextElement::create(Translation::getTranslation("translations"))
                            ->setTagName("a")
                            ->addAttribute("href", BASE_URL."/admin/manage/translation")
                        )
            )->addNavItem(
                NavItem::create(
                    "fa fa-chart-area",
                    Translation::getTranslation("tables"),
                    BASE_URL."/admin/table"
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
        $default_js_files = [
            "src/vendor/js/jquery.js",
            "src/vendor/js/jquery-easing.js",
            "src/vendor/js/popper.min.js",
            "src/vendor/js/bootstrap.min.js",
            "src/vendor/js/bootstrap-select.js",
            "src/vendor/js/moment.js",
            "src/vendor/js/bootstrap-datetimepicker.min.js",
            "src/vendor/js/bootstrap-dialog.min.js",
            "src/vendor/js/summernote.js",
            "src/vendor/js/summernote-tr-TR.js",
            "src/vendor/js/sb-admin-2.js",
            "src/js/core.js",
            "src/vendor/js/daterangepicker.min.js",
        ];
        if (class_exists("Translation") && Translation::getLanguage() != "en") {
            $default_js_files[] = "src/vendor/js/bootstrap-select.".Translation::getLanguage().".js";
            $default_js_files[] = "src/vendor/js/moment.".Translation::getLanguage().".js";
        }
        $this->js_files = array_merge($default_js_files, $this->js_files);
    }
    protected function addDefaultCssFiles()
    {
        $default_css_files = [
            "src/vendor/css/bootstrap.min.css",
            "src/vendor/css/sb-admin-2.css",
            "src/vendor/css/bootstrap-select.min.css",
            "src/vendor/css/bootstrap-datetimepicker.min.css",
            "src/vendor/css/bootstrap-dialog.min.css",
            "src/vendor/css/summernote.css",
            "src/vendor/css/fontawesome/css/all.min.css",
            "src/vendor/css/daterangepicker.css",
            "src/css/core.css"
        ];
        $this->css_files = array_merge($default_css_files, $this->css_files);
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
