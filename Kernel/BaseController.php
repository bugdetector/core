<?php

namespace CoreDB\Kernel;

use Exception;
use Src\BaseTheme\BaseTheme;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Theme\ThemeInteface;
use Src\Views\AlertMessage;
use Src\Views\ViewGroup;

abstract class BaseController implements ControllerInterface
{
    public $arguments = [];
    public array $actions = [];
    public $method;

    public string $title = "";
    public array $metaTags = [];
    public $js_files = [];
    public $js_codes = [];
    public $css_files = [];
    public $css_codes = [];
    public $frontend_translations = [];

    public function getTheme(): ThemeInteface
    {
        $themeClass = defined("THEME") ? THEME : BaseTheme::class;
        return new $themeClass();
    }

    public function processPage()
    {
        $theme = $this->getTheme();
        $theme->setDefaults($this);
        $this->preprocessPage();
        $theme->render($this);
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTemplateFile(): string
    {
        return "page.twig";
    }

    /**
     * Base controller class construction
     * @param array $arguments
     *  Page arguments
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
        $this->method = isset($this->arguments[0]) ? $this->arguments[0] : null;
        $siteName = Variable::getByKey("site_name")->value->getValue();
        $this->setTitle($siteName);
        $this->addMetaTag("description", [
            "name" => "description",
            "content" => $siteName
        ]);

        $httpAuthorizationHeader = @$_SERVER["HTTP_AUTHORIZATION"] ?: (
            @$_SERVER["REDIRECT_HTTP_AUTHORIZATION"] ?: @$_SERVER["REDIRECT_REDIRECT_HTTP_AUTHORIZATION"]
        );
        if (defined("HTTP_AUTH_ENABLED") && HTTP_AUTH_ENABLED) {
            if ($httpAuthorizationHeader && !@$_SERVER['PHP_AUTH_USER'] && !@$_SERVER['PHP_AUTH_PW']) {
                list(
                    $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']
                ) = explode(':', base64_decode(substr($httpAuthorizationHeader, 6)));
            }
            if (
                @$_SERVER['PHP_AUTH_USER'] !== HTTP_AUTH_USERNAME ||
                @$_SERVER['PHP_AUTH_PW'] !== HTTP_AUTH_PASSWORD
            ) {
                header("WWW-Authenticate: Basic realm=\"Coredb Auth\"");
                header("HTTP/1.0 401 Unauthorized");
                die();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function checkAccess(): bool
    {
        return true;
    }
    public function echoContent()
    {
    }
    /**
     * @inheritdoc
     */
    public function printMessages(): ViewGroup
    {
        $types = [
            Messenger::ERROR => "alert-danger",
            Messenger::WARNING => "alert-warning",
            Messenger::INFO => "alert-info",
            Messenger::SUCCESS => "alert-success"
        ];
        $messages = \CoreDB::messenger()->getMessages();
        $message_group = new ViewGroup("div", "messages");
        if (!empty($messages)) {
            foreach ($types as $type_key => $type) {
                if (isset($messages[$type_key])) {
                    foreach ($messages[$type_key] as $key => $message) {
                        $message_group->addField(AlertMessage::create($message, $type));
                    }
                }
            }
        }
        \CoreDB::messenger()->clearMessages();
        return $message_group;
    }

    public static function getUrl(): string
    {
        return Router::getInstance()->getUrl(static::class);
    }

    /**
     * @inheritdoc
     */
    public function createMessage($message, int $type = Messenger::ERROR): void
    {
        \CoreDB::messenger()->createMessage($message, $type);
    }

    public function preprocessPage()
    {
        try {
            if ($this->method && method_exists($this, $this->method)) {
                $this->{$this->method}();
            }
        } catch (Exception $ex) {
            $this->createMessage($ex->getMessage());
        }
    }

    public function addJsFiles($js_file_path)
    {
        if (is_array($js_file_path)) {
            $this->js_files = array_unique(array_merge($this->js_files, $js_file_path));
        } elseif (!in_array($js_file_path, $this->js_files)) {
            $this->js_files[] = $js_file_path;
        }
    }

    public function addMetaTag($index, $attributes)
    {
        $this->metaTags[$index] = $attributes;
    }

    public function addJsCode(string $js_code)
    {
        $this->js_codes[] = $js_code;
    }

    public function addCssFiles($css_file_path)
    {
        if (is_array($css_file_path)) {
            $this->css_files = array_unique(array_merge($this->css_files, $css_file_path));
        } elseif (!in_array($css_file_path, $this->css_files)) {
            $this->css_files[] = $css_file_path;
        }
    }

    public function addCssCode(string $css_code)
    {
        $this->css_codes[] = $css_code;
    }

    public function addFrontendTranslation($translation_key)
    {
        $this->frontend_translations[$translation_key] = Translation::getTranslation($translation_key);
    }
}
