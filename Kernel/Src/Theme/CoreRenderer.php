<?php

namespace Src\Theme;

use Src\Theme\CoreTwigExtension;
use CoreDB\Kernel\BaseController;
use CoreDB\Kernel\ControllerInterface;
use Src\Theme\View;
use Src\Form\Form;
use Src\Form\Widget\FormWidget;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class CoreRenderer
{
    private static $instance;

    public \Twig\Environment $twig;
    private function __construct($template_directories)
    {
        $loader = new FilesystemLoader($template_directories);
        $twig_options = [];
        $enviroment = defined("ENVIROMENT") ? ENVIROMENT : "development";
        if (in_array($enviroment, ["production", "staging"])) {
            $twig_options["cache"] = "../cache";
        } else {
            $twig_options["debug"] = true;
        }
        $this->twig = new Environment($loader, $twig_options);
        $this->twig->addExtension(new CoreTwigExtension());
        if ($enviroment == "development") {
            $this->twig->addExtension(new DebugExtension());
        }
    }

    public static function getInstance(array $template_directories): CoreRenderer
    {
        if (!self::$instance) {
            self::$instance = new CoreRenderer($template_directories);
        }
        return self::$instance;
    }

    public function renderController(ThemeInteface $theme, ControllerInterface $controller)
    {
        return $this->twig->render($controller->getTemplateFile(), [
            "theme" => $theme,
            "controller" => $controller
        ]);
    }

    public function renderView(View $view)
    {
        return $this->twig->render("views/" . $view->getTemplateFile(), [
            "view" => $view,
        ]);
    }

    public function renderForm(Form $form)
    {
        return $this->twig->render("forms/" . $form->getTemplateFile(), [
            "form" => $form,
        ]);
    }

    public function renderWidget(FormWidget $widget)
    {
        return $this->twig->render("widgets/" . $widget->getTemplateFile(), [
            "widget" => $widget,
        ]);
    }
}
