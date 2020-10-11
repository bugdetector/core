<?php

namespace Src\Theme;

use Src\Theme\CoreTwigExtension;
use CoreDB\Kernel\BaseController;
use Src\Theme\View;
use Src\Form\Form;
use Src\Form\Widget\FormWidget;

class CoreRenderer
{
    private static $instance;

    public \Twig\Environment $twig;
    private function __construct($template_directories)
    {
        $loader = new \Twig\Loader\FilesystemLoader($template_directories);
        $twig_options = [];
        $enviroment = defined("ENVIROMENT") ? ENVIROMENT : "development";
        if ($enviroment == "production") {
            $twig_options["cache"] = "../cache";
        } else {
            $twig_options["debug"] = true;
        }
        $this->twig = new \Twig\Environment($loader, $twig_options);
        $this->twig->addExtension(new CoreTwigExtension());
        if ($enviroment == "development") {
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }
    }

    public static function getInstance(array $template_directories): CoreRenderer
    {
        if (!self::$instance) {
            self::$instance = new CoreRenderer($template_directories);
        }
        return self::$instance;
    }

    public function renderController(BaseController $controller)
    {
        echo $this->twig->render($controller->getTemplateFile(), [
            "controller" => $controller,
        ]);
    }

    public function renderView(View $view)
    {
        echo $this->twig->render("views/" . $view->getTemplateFile(), [
            "view" => $view,
        ]);
    }

    public function renderForm(Form $form)
    {
        echo $this->twig->render("forms/" . $form->getTemplateFile(), [
            "form" => $form,
        ]);
    }

    public function renderWidget(FormWidget $widget)
    {
        echo $this->twig->render("widgets/" . $widget->getTemplateFile(), [
            "widget" => $widget,
        ]);
    }
}
