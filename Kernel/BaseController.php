<?php

namespace CoreDB\Kernel;

use Exception;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Theme\CoreRenderer;
use Src\Views\AlertMessage;
use Src\Views\ViewGroup;

abstract class BaseController implements ControllerInterface
{

    public $arguments = [];
    public $method;
    
    public string $title = "";
    public $js_files = [];
    public $js_codes = [];
    public $css_files = [];
    public $css_codes = [];
    public $frontend_translations = [];

    abstract public static function getTemplateDirectories() : array;

    public function render()
    {
        CoreRenderer::getInstance($this::getTemplateDirectories())->renderController($this);
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    public function getTemplateFile() : string
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
        $siteName = Variable::getByKey("site_name");
        $this->setTitle($siteName ? $siteName->value : "CoreDB");
    }

    /**
     * @inheritdoc
     */
    public function checkAccess(): bool
    {
        return true;
    }
    /**
     * @inheritdoc
     */
    public function printMessages() : ViewGroup
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

    public static function getUrl() : string{
        return Router::getInstance()->getUrl(static::class);
    }

    /**
     * @inheritdoc
     */
    public function createMessage(string $message, int $type = Messenger::ERROR): void
    {
        \CoreDB::messenger()->createMessage($message, $type);
    }

    public function preprocessPage()
    {
        try {
            if ($this->method) {
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
        } else if(!in_array($js_file_path, $this->js_files)){
            $this->js_files[] = $js_file_path;
        }
    }

    public function addJsCode(string $js_code)
    {
        if (!$this->js_codes) {
            $this->js_codes = [];
        }
        $this->js_codes[] = $js_code;
    }
    
    public function addCssFiles($css_file_path)
    {
        if (is_array($css_file_path)) {
            $this->css_files = array_unique(array_merge($this->css_files, $css_file_path));
        } else if(!in_array($css_file_path, $this->css_files)){
            $this->css_files[] = $css_file_path;
        }
    }
    
    public function addFrontendTranslation($translation_key)
    {
        $this->frontend_translations[$translation_key] = Translation::getTranslation($translation_key);
    }
}
