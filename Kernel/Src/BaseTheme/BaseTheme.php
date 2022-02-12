<?php

namespace Src\BaseTheme;

use CoreDB\Kernel\ControllerInterface;
use Src\Theme\CoreRenderer;
use Src\Theme\ThemeInteface;
use Src\Views\Navbar;
use Src\Views\Sidebar;

class BaseTheme implements ThemeInteface
{

    public Navbar $navbar;
    public Sidebar $sidebar;

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
        echo CoreRenderer::getInstance(
            $this::getTemplateDirectories()
        )->renderController($this, $controller);
    }

    public function buildNavbar()
    {
        $this->navbar = Navbar::create();
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
        $controller->addJsFiles("base_theme/assets/plugins/global/plugins.bundle.js");
        $controller->addJsFiles("base_theme/assets/js/scripts.bundle.js");
        $controller->addJsFiles("assets/js/coredb.js");
    }

    protected function addDefaultCssFiles(ControllerInterface $controller)
    {
        $controller->addCssFiles("base_theme/assets/plugins/global/plugins.bundle.css");
        $controller->addCssFiles("base_theme/assets/css/style.bundle.css");
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
