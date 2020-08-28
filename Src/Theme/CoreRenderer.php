<?php
namespace Src\Theme;

use App\Twig\CoreTwigExtension;
use CoreDB\Kernel\ControllerInterface;
use Src\Theme\View;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\Form;
use Src\Form\Widget\FormWidget;

class CoreRenderer
{
    private static $instance;

    public \Twig\Environment $twig;
    private function __construct($template_directories)
    {
        $loader = new \Twig\Loader\FilesystemLoader($template_directories);
        $this->twig = new \Twig\Environment($loader, [
            'cache' => '../cache'
        ]);
        $this->twig->addExtension(new CoreTwigExtension());
    }

    public static function getInstance(array $template_directories) : CoreRenderer
    {
        if (!self::$instance) {
            self::$instance = new CoreRenderer($template_directories);
        }
        return self::$instance;
    }

    public function renderController(ControllerInterface $controller)
    {
        echo $this->twig->render($controller->getTemplateFile(), [
            "controller" => $controller,
            "user" => User::get_current_core_user(),
            "Translation" => new Translation()
        ]);
    }

    public function renderView(View $view)
    {
        echo $this->twig->render("views/".$view->getTemplateFile(), [
            "view" => $view,
            "user" => User::get_current_core_user(),
            "Translation" => new Translation()
        ]);
    }

    public function renderForm(Form $form)
    {
        echo $this->twig->render("forms/".$form->getTemplateFile(), [
            "form" => $form,
            "user" => User::get_current_core_user(),
            "Translation" => new Translation()
        ]);
    }

    public function renderWidget(FormWidget $widget)
    {
        echo $this->twig->render("widgets/".$widget->getTemplateFile(), [
            "widget" => $widget,
            "user" => User::get_current_core_user(),
            "Translation" => new Translation()
        ]);
    }
}
