<?php

namespace Src\BaseTheme;

use CoreDB\Kernel\ControllerInterface;
use CoreDB\Kernel\Database\DatabaseInstallationException;
use Src\Theme\CoreRenderer;
use Src\Theme\ThemeInteface;
use Src\Views\Navbar;
use Src\Views\Sidebar;

class BaseTheme implements ThemeInteface
{
    public Navbar $navbar;
    public Sidebar $sidebar;
    public bool $darkTheme = false;

    public static function getTemplateDirectories(): array
    {
        return [__DIR__ . "/templates"];
    }

    public function setDefaults(ControllerInterface $controller)
    {
        $this->buildNavbar();
        $this->buildSidebar();
        $this->addDefaultMetaTags($controller);
        $this->addDefaultJsFiles($controller);
        $this->addDefaultCssFiles($controller);
        $this->addDefaultTranslations($controller);
    }

    public function render(ControllerInterface $controller)
    {
        echo CoreRenderer::getInstance($this)
        ->renderController($controller);
    }

    public function buildNavbar()
    {
        $this->navbar = Navbar::create();
    }

    public function buildSidebar()
    {
        $this->sidebar = Sidebar::create();
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
        $controller->addJsFiles("base_theme/assets/js/scripts.bundle.js");
        $controller->addJsFiles("base_theme/assets/plugins/global/plugins.bundle.js");
        $controller->addJsFiles("assets/js/coredb.js");
        $controller->addJsFiles("base_theme/assets/plugins/custom/prismjs/prismjs.bundle.js");
        if (defined("PWA_ENABLED") && PWA_ENABLED) {
            $controller->addJsFiles("pwa_registerer.js");
            $controller->addJsCode("NOTIFICATIONS_ENABLED = " . var_export(boolval(
                \CoreDB::currentUser()->isLoggedIn() && defined("NOTIFICATIONS_ENABLED") && NOTIFICATIONS_ENABLED
            ), true));
            $controller->addJsCode("PN_DENIED = " . var_export(boolval(@$_SESSION["PN_DENIED"]), true));
        }
    }

    protected function addDefaultCssFiles(ControllerInterface $controller)
    {
        if (isset($_COOKIE["dark-mode"]) && filter_var($_COOKIE["dark-mode"], FILTER_VALIDATE_BOOL)) {
            $controller->addCssFiles("base_theme/assets/plugins/global/plugins.dark.bundle.css");
            $controller->addCssFiles("base_theme/assets/css/style.dark.bundle.css");
            $this->darkTheme = true;
        } else {
            $controller->addCssFiles("base_theme/assets/plugins/global/plugins.bundle.css");
            $controller->addCssFiles("base_theme/assets/css/style.bundle.css");
        }
        $controller->addCssFiles("base_theme/assets/plugins/custom/prismjs/prismjs.bundle.css");
        $controller->addJsCode("var darkMode = " . var_export($this->darkTheme, true) . ";");
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
        try {
            if (\CoreDB::currentUser()->isLoggedIn() && defined("NOTIFICATIONS_ENABLED") && NOTIFICATIONS_ENABLED) {
                $controller->addFrontendTranslation("subscribe_notifications");
                $controller->addFrontendTranslation("subscribe_notifications_message");
                $controller->addFrontendTranslation("thanks");
                $controller->addFrontendTranslation("allow_notifications");
            }
        } catch (DatabaseInstallationException $ex) {
        }
    }

    public function canonicalUrl()
    {
        return BASE_URL . \CoreDB::requestUrl();
    }
}
