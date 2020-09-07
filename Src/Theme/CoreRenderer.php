<?php
namespace Src\Theme;

use App\Twig\CoreTwigExtension;
use CoreDB\Kernel\BaseControllerInterface;
use Src\Theme\View;
use Src\Entity\Translation;
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
        if (ENVIROMENT == "production") {
            $twig_options["cache"] = "../cache";
        }
        $this->twig = new \Twig\Environment($loader, $twig_options);
        $this->twig->addExtension(new CoreTwigExtension());
    }

    public static function getInstance(array $template_directories) : CoreRenderer
    {
        if (!self::$instance) {
            self::$instance = new CoreRenderer($template_directories);
        }
        return self::$instance;
    }

    public function renderController(BaseControllerInterface $controller)
    {
        echo $this->twig->render($controller->getTemplateFile(), [
            "controller" => $controller,
            "user" => \CoreDB::currentUser(),
            "Translation" => Translation::getInstance()
        ]);
    }

    public function renderView(View $view)
    {
        echo $this->twig->render("views/".$view->getTemplateFile(), [
            "view" => $view,
            "user" => \CoreDB::currentUser(),
            "Translation" => Translation::getInstance()
        ]);
    }

    public function renderForm(Form $form)
    {
        echo $this->twig->render("forms/".$form->getTemplateFile(), [
            "form" => $form,
            "user" => \CoreDB::currentUser(),
            "Translation" => Translation::getInstance()
        ]);
    }

    public function renderWidget(FormWidget $widget)
    {
        echo $this->twig->render("widgets/".$widget->getTemplateFile(), [
            "widget" => $widget,
            "user" => \CoreDB::currentUser(),
            "Translation" => Translation::getInstance()
        ]);
    }
}
