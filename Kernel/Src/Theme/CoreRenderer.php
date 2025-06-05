<?php

namespace Src\Theme;

use Src\Theme\CoreTwigExtension;
use CoreDB\Kernel\ControllerInterface;
use Src\BaseTheme\BaseTheme;
use Src\Theme\View;
use Src\Form\Form;
use Src\Form\Widget\FormWidget;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\FilesystemLoader;

class CoreRenderer
{
    private static $instance;

    public \Twig\Environment $twig;
    public ThemeInteface $theme;
    private function __construct(ThemeInteface $theme)
    {
        $this->theme = $theme;
        $loader = new FilesystemLoader($this->theme->getTemplateDirectories());
        $twig_options = [];
        $enviroment = defined("ENVIROMENT") ? ENVIROMENT : "development";
        if (in_array($enviroment, ["production", "staging"])) {
            $twig_options["cache"] = "../cache";
        } else {
            $twig_options["debug"] = true;
        }
        $this->twig = new Environment($loader, $twig_options);
        $this->twig->addExtension(new CoreTwigExtension());
        $this->twig->addExtension(new StringLoaderExtension());

        $extensions = Yaml::parseFile(__DIR__ . "/../../../config/twig_extensions.yml");
        foreach ($extensions ?: [] as $extension) {
            $this->twig->addExtension(new $extension());
        }

        if ($enviroment == "development") {
            $this->twig->addExtension(new DebugExtension());
        }
    }

    public static function getInstance(?ThemeInteface $theme = null): CoreRenderer
    {
        if (!self::$instance) {
            if (!$theme) {
                $themeClass = defined("THEME") ? THEME : BaseTheme::class;
                $theme = new $themeClass();
            }
            self::$instance = new CoreRenderer($theme);
        } elseif ($theme) {
            self::$instance->theme = $theme;
        }
        return self::$instance;
    }

    public function renderController(ControllerInterface $controller)
    {
        return $this->twig->render($controller->getTemplateFile(), [
            "theme" => $this->theme,
            "controller" => $controller
        ]);
    }

    public function renderView(View $view)
    {
        return $this->twig->render("views/" . $view->getTemplateFile(), [
            "theme" => $this->theme,
            "view" => $view,
        ]);
    }

    public function renderForm(Form $form)
    {
        return $this->twig->render("forms/" . $form->getTemplateFile(), [
            "theme" => $this->theme,
            "form" => $form,
        ]);
    }

    public function renderWidget(FormWidget $widget)
    {
        return $this->twig->render("widgets/" . $widget->getTemplateFile(), [
            "theme" => $this->theme,
            "widget" => $widget,
        ]);
    }
}
