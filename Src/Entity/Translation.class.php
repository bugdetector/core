<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\TableMapper;
use Exception;

class Translation extends TableMapper
{
    private static $language;
    private static $cache;
    private static $available_languages;

    const BACKUP_PATH = __DIR__ . "/../../translations/translations.json";

    const TABLE = "translations";
    public $ID;
    public $key;
    public $en;
    public $tr;
    public $created_at;
    public $last_updated;


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

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @Override
     */
    public static function get(array $filter) : ?Translation
    {
        return parent::find($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter): array
    {
        return parent::findAll($filter, self::TABLE);
    }

    public static function clear(){
        parent::clearTable(self::TABLE);
    }

    public function map(array $array)
    {
        parent::map($array);
        foreach (array_keys($array) as $field_name) {
            $this->{$field_name} = htmlspecialchars_decode($this->{$field_name});
        }
    }

    public static function getTranslation($key, array $arguments = NULL)
    {
        if (!isset(self::$cache[$key])) {
            $translation = Translation::get(["key" => $key]);
            self::$cache[$key] = $translation ? $translation->{Translation::getLanguage()} : $key;
        }
        return !$arguments ? self::$cache[$key] : vsprintf(self::$cache[$key], $arguments);
    }

    public static function getEmailTranslation(string $key, array $arguments = null)
    {
        $mail = Email::get(["key" => $key]);
        $translation = $mail->{Translation::getLanguage()};
        if($translation && $arguments){
            $translation = str_replace("http://%s", "%s", $translation);
            $translation = vsprintf($translation, $arguments);
        }
        return $translation;
    }

    public static function getAvailableLanguageList()
    {
        if (true || !isset(self::$available_languages)) {
            try {
                $table_description = \CoreDB::database()::getTableDescription(Translation::TABLE);
                self::$available_languages = [];
                /**
                 * @var DataTypeAbstract $column_description
                 */
                foreach ($table_description as $column_description) {
                    if(in_array($column_description->column_name, ["ID", "key", "created_at", "last_updated"])){
                        continue;
                    }
                    self::$available_languages[] = $column_description->column_name;
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
        \CoreDB::database()::getInstance()->beginTransaction();
        db_truncate(self::TABLE);
        foreach ($translations as $translation) {
            $translate = new Translation();
            $translate->map($translation);
            $translate->insert();
        }
        \CoreDB::database()::getInstance()->commit();
    }

    public static function exportTranslations()
    {
        $translations = Translation::getAll([]);
        if (file_exists(Translation::BACKUP_PATH)) {
            unlink(Translation::BACKUP_PATH);
        }
        foreach ($translations as $translation) {
            unset($translation->ID, $translation->created_at, $translation->last_updated, $translation->table);
        }
        file_put_contents(Translation::BACKUP_PATH, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
