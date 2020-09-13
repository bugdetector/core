<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\TableMapper;
use Exception;
use PDO;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class Translation extends TableMapper
{
    private static $language;
    private static $cache;
    private static $available_languages;
    private static $instance;

    const BACKUP_PATH = __DIR__ . "/../../translations/translations.json";

    public ShortText $key;
    public ShortText $en;
    public ShortText $tr;


    public static function getLanguage()
    {
        if (!Translation::$language) {
            $supportedLangs = Translation::getAvailableLanguageList();
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($languages as $language) {
                $language = preg_replace("/-.*/", "", $language);
                if (in_array($language, $supportedLangs)) {
                    // Set the page locale to the first supported language found
                    Translation::$language = $language;
                    break;
                }
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

    public static function getInstance() : Translation{
        if(!self::$instance){
            self::$instance = new Translation();
        }
        return self::$instance;
    }

    public static function getTranslation($key, array $arguments = null)
    {
        if (!isset(self::$cache[$key])) {
            $translation = Translation::get(["key" => $key]);
            self::$cache[$key] = $translation ? $translation->{Translation::getLanguage()}->getValue() : $key;
        }
        return !$arguments ? self::$cache[$key] : vsprintf(self::$cache[$key], $arguments);
    }

    public static function getEmailTranslation(string $key, array $arguments = null)
    {
        $mail = Email::get(["key" => $key]);
        $translation = $mail->{Translation::getLanguage()};
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
            } catch (Exception $ex) {
                self::$available_languages[] = "en";
            }
        }
        return self::$available_languages;
    }

    public static function importTranslations()
    {
        $translations = json_decode(file_get_contents(Translation::BACKUP_PATH), true);
        \CoreDB::database()->beginTransaction();
        self::clear();
        foreach ($translations as $translation) {
            $translate = new Translation();
            $translate->map($translation);
            $translate->insert();
        }
        \CoreDB::database()->commit();
    }

    public static function exportTranslations()
    {
        $translations = \CoreDB::database()
        ->select(self::getTableName())
        ->execute()
        ->fetchAll(PDO::FETCH_OBJ);
        if (file_exists(Translation::BACKUP_PATH)) {
            unlink(Translation::BACKUP_PATH);
        }
        foreach ($translations as $translation) {
            unset($translation->ID, $translation->created_at, $translation->last_updated);
        }
        file_put_contents(Translation::BACKUP_PATH, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @inheritdoc
     */
    public function getTableHeaders(bool $translateLabel = true) : array{
        $headers = parent::getTableHeaders($translateLabel);
        unset($headers["ID"], $headers["created_at"], $headers["last_updated"]);
        return $headers;
    }
    /**
     * @inheritdoc
     */
    public function getSearchFormFields(bool $translateLabel = true) : array{
        $fields = parent::getSearchFormFields($translateLabel);
        unset($fields["ID"], $fields["created_at"], $fields["last_updated"]);
        return $fields;
    }
    /**
     * @inheritdoc
     */
    public function getTableQuery() : SelectQueryPreparerAbstract{
        $fields = array_merge(["ID AS edit_actions", "key"], $this->getAvailableLanguageList());
        return \CoreDB::database()->select($this->getTableName(), "t")
        ->select("t", $fields);
    }

    public function actions(): array
    {
        $actions = parent::actions();
        $actions[] = ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-1 lang-imp")
        ->addField(
            ViewGroup::create("i", "fa fa-file-import text-white-50")
        )->addAttribute("href", "#")
        ->addField(TextElement::create(Translation::getTranslation("import")));
        $actions[] = ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-1 lang-exp")
        ->addField(
            ViewGroup::create("i", "fa fa-file-export text-white-50")
        )->addAttribute("href", "#")
        ->addField(TextElement::create(Translation::getTranslation("export")));

        \CoreDB::controller()->addJsFiles("src/js/translation.js");
        \CoreDB::controller()->addFrontendTranslation("lang_import_info");
        \CoreDB::controller()->addFrontendTranslation("lang_export_info");

        return $actions;
    }
}
