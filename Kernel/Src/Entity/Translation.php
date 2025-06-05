<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DatabaseInstallationException;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\Model;
use DirectoryIterator;
use PDO;
use Src\Form\Widget\InputWidget;
use Src\Theme\View;
use Src\Views\TextElement;
use Symfony\Component\Yaml\Yaml;

class Translation extends Model
{
    private static $language;
    private static $cache;
    private static $available_languages;
    private static $instance;

    private const BACKUP_PATH = __DIR__ . "/../../../config/translations";

    public ShortText $key;
    public LongText $en;
    public LongText $tr;


    public static function getLanguage()
    {
        $supportedLangs = Translation::getAvailableLanguageList();
        if (isset($_GET["lang"]) && in_array($_GET["lang"], $supportedLangs)) {
            $_SESSION["lang"] = $_GET["lang"];
        }
        if (isset($_SESSION["lang"]) && in_array($_SESSION["lang"], $supportedLangs)) {
            Translation::$language = $_SESSION["lang"];
        }
        if (!Translation::$language) {
            $languages = explode(',', !IS_CLI ? @$_SERVER['HTTP_ACCEPT_LANGUAGE'] : $_SERVER["LANG"]);
            foreach ($languages as $language) {
                $language = preg_replace(!IS_CLI ? "/-.*/" : "/_.*/", "", $language);
                if (in_array($language, $supportedLangs)) {
                    // Set the page locale to the first supported language found
                    Translation::$language = $language;
                    break;
                }
            }
            if (!Translation::$language) {
                Translation::$language = LANGUAGE;
            }
        }
        return self::$language;
    }

   /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "translations";
    }

    public static function getInstance(): Translation
    {
        if (!self::$instance) {
            self::$instance = new Translation();
        }
        return self::$instance;
    }

    public static function getTranslation($key, ?array $arguments = null, $useLanguage = null)
    {
        if (!isset(self::$cache[$key])) {
            try {
                $translation = Translation::get(["key" => $key]);
                $language = $useLanguage ?: Translation::getLanguage();
            } catch (DatabaseInstallationException $ex) {
                $language = Translation::getLanguage();
                self::$cache = Yaml::parseFile(Translation::BACKUP_PATH . "/{$language}.yml");
                return isset(self::$cache[$key]) ? self::$cache[$key] : $key;
            }
            self::$cache[$key] = $translation ? $translation->$language->getValue() : $key;
        }
        return !$arguments ? self::$cache[$key] : vsprintf(self::$cache[$key], $arguments);
    }

    public static function getEmailTranslation(string $key, ?array $arguments = null, $useLanguage = null)
    {
        $mail = Email::get(["key" => $key]);
        $language = $useLanguage ?: Translation::getLanguage();
        $translation = $mail->$language;
        if ($translation && $arguments) {
            $translation = str_replace("http://%s", "%s", $translation);
            $translation = vsprintf($translation, $arguments);
        }
        return $translation;
    }

    public static function getAvailableLanguageList()
    {
        if (true || !isset(self::$available_languages)) {
            try {
                self::$available_languages = [];
                /**
                 * @var DataTypeAbstract $column_description
                 */
                foreach (TableDefinition::getDefinition(self::getTableName())->fields as $field_name => $field) {
                    if (in_array($field_name, ["ID", "key", "created_at", "last_updated"])) {
                        continue;
                    }
                    self::$available_languages[] = $field_name;
                }
            } catch (DatabaseInstallationException $ex) {
                /**
                 * @var DirectoryIterator $fileInfo
                 */
                foreach (new DirectoryIterator(self::BACKUP_PATH) as $fileInfo) {
                    if (!$fileInfo->isDot()) {
                        if (!$fileInfo->isDir()) {
                            self::$available_languages[] = pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME);
                        }
                    }
                }
            }
        }
        return self::$available_languages;
    }

    public static function importTranslations()
    {
        foreach (self::getAvailableLanguageList() as $language) {
            $importPath = Translation::BACKUP_PATH . "/{$language}.yml";
            if (is_file($importPath)) {
                $translations = Yaml::parseFile($importPath);
                foreach ($translations as $key => $translation) {
                    $record = Translation::get(["key" => $key ]) ? : new Translation();
                    $record->key->setValue($key);
                    $record->{$language}->setValue($translation);
                    $record->save();
                }
            }
        }
    }

    public static function exportTranslations()
    {
        foreach (self::getAvailableLanguageList() as $language) {
            $translations = \CoreDB::database()
            ->select(self::getTableName(), "t")
            ->select("t", ["key", $language])
            ->execute()
            ->fetchAll(PDO::FETCH_KEY_PAIR);
            $exportPath = self::BACKUP_PATH . "/{$language}.yml";
            file_put_contents($exportPath, Yaml::dump($translations));
        }
    }

    /**
     * @inheritdoc
     */
    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        unset($headers["ID"], $headers["created_at"], $headers["last_updated"]);
        return $headers;
    }
    /**
     * @inheritdoc
     */
    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $fields = parent::getSearchFormFields($translateLabel);
        unset($fields["ID"], $fields["created_at"], $fields["last_updated"]);
        return $fields;
    }
    /**
     * @inheritdoc
     */
    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $fields = array_merge(["ID AS edit_actions", "key"], $this->getAvailableLanguageList());
        return \CoreDB::database()->select($this->getTableName(), "t")
        ->select("t", $fields);
    }

    public function getForm()
    {
        \CoreDB::controller()->addJsCode(
            "let editor = null;
            $(function(){
                $(document).on('click', '.html_toggle, .raw_toggle', function(e){
                    e.preventDefault();
                    if($(this).hasClass('html_toggle')){
                        $(this).text('RAW');
                        loadHtmlEditor($(this).closest('div').find('textarea')[0]).then( instance => {
                            editor = instance;
                        } );
                    }else{
                        $(this).text('HTML');
                        editor[0].destroy();
                    }
                    $(this).toggleClass('html_toggle raw_toggle');
                });
            })"
        );
        return parent::getForm();
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        /** @var InputWidget */
        $widget = parent::getFieldWidget($field_name, $translateLabel);
        if ($field_name != "key") {
            $widget->removeClass("html-editor");
            $widget->setDescription(
                TextElement::create(
                    "<button class='btn btn-sm btn-primary mt-2 html_toggle'>
                    HTML
                    </button> <br>" . $widget->description
                )->setIsRaw(true)
            );
        }
        return $widget;
    }
}
