<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DatabaseInstallationException;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\TableMapper;
use DirectoryIterator;
use Exception;
use PDO;
use Src\Views\TextElement;
use Src\Views\ViewGroup;
use Symfony\Component\Yaml\Yaml;

class Translation extends TableMapper
{
    private static $language;
    private static $cache;
    private static $available_languages;
    private static $instance;

    const BACKUP_PATH = __DIR__ . "/../../config/translations";

    public ShortText $key;
    public Text $en;
    public Text $tr;


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
            try{
                $translation = Translation::get(["key" => $key]);
            }catch(DatabaseInstallationException $ex){
                $language = Translation::getLanguage();
                self::$cache = Yaml::parseFile(Translation::BACKUP_PATH."/{$language}.yml");
                return isset(self::$cache[$key]) ? self::$cache[$key] : $key;
            }
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
        foreach(self::getAvailableLanguageList() as $language){
            $importPath = Translation::BACKUP_PATH."/{$language}.yml";
            if(is_file($importPath)){
                $translations = Yaml::parseFile($importPath);
                foreach($translations as $key => $translation){
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
        foreach(self::getAvailableLanguageList() as $language){
            $translations = \CoreDB::database()
            ->select(self::getTableName(), "t")
            ->select("t", ["key", $language])
            ->execute()
            ->fetchAll(PDO::FETCH_KEY_PAIR);
            $exportPath = self::BACKUP_PATH."/{$language}.yml";
            file_put_contents($exportPath, Yaml::dump($translations));
        }
    }

    /**
     * @inheritdoc
     */
    public function getResultHeaders(bool $translateLabel = true) : array{
        $headers = parent::getResultHeaders($translateLabel);
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
    public function getResultQuery() : SelectQueryPreparerAbstract{
        $fields = array_merge(["ID AS edit_actions", "key"], $this->getAvailableLanguageList());
        return \CoreDB::database()->select($this->getTableName(), "t")
        ->select("t", $fields);
    }

    public function actions(): array
    {
        $actions = parent::actions();
        $actions[] = ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-1 mb-1 lang-imp")
        ->addField(
            ViewGroup::create("i", "fa fa-file-import text-white-50")
        )->addAttribute("href", "#")
        ->addField(TextElement::create(Translation::getTranslation("import")));
        $actions[] = ViewGroup::create("a", "d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-1 mb-1 lang-exp")
        ->addField(
            ViewGroup::create("i", "fa fa-file-export text-white-50")
        )->addAttribute("href", "#")
        ->addField(TextElement::create(Translation::getTranslation("export")));

        \CoreDB::controller()->addJsFiles("dist/translation_screen/translation_screen.js");
        \CoreDB::controller()->addFrontendTranslation("lang_import_info");
        \CoreDB::controller()->addFrontendTranslation("lang_export_info");

        return $actions;
    }
}
