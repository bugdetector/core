<?php

/**
 * 
 * @param int|string $id
 * @param array $arguments
 * @return string
 */

function _t($key, array $arguments = NULL)
{
    $translation = Translation::getTranslation($key);
    if ($arguments) {
        $translation = vsprintf($translation, $arguments);
    }
    return $translation;
}

function _t_email(string $key, array $arguments = NULL)
{
    $translation = htmlspecialchars_decode(Translation::getEmailTranslation($key));
    $translation = str_replace("http://%s", "%s", $translation);
    if ($arguments) {
        $translation = vsprintf($translation, $arguments);
    }
    return $translation;
}


class Translation extends DBObject
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
    public static function get(array $filter, string $table = self::TABLE) : ?Translation
    {
        return parent::get($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter, string $table = self::TABLE): array
    {
        return parent::getAll($filter, $table);
    }

    public function map(array $array)
    {
        parent::map($array);
        foreach (array_keys($array) as $field_name) {
            $this->{$field_name} = htmlspecialchars_decode($this->{$field_name});
        }
    }

    public static function getTranslation($key)
    {
        if (!isset(self::$cache[$key])) {
            $translation = Translation::get(["key" => $key]);
            self::$cache[$key] = $translation ? $translation->{Translation::getLanguage()} : $key;
        }
        return self::$cache[$key];
    }

    public static function getEmailTranslation(string $key)
    {
        $mail = Email::get(["key" => $key]);
        return $mail->{Translation::getLanguage()};
    }

    public static function getAvailableLanguageList()
    {
        if (!isset(self::$available_languages)) {
            try {
                $translation_table_description = CoreDB::get_table_description(Translation::TABLE, false);
                unset($translation_table_description[0]); // ID column removed
                self::$available_languages = [];
                foreach ($translation_table_description as $column_description) {
                    self::$available_languages[] = $column_description["Field"];
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
        CoreDB::getInstance()->beginTransaction();
        db_truncate(self::TABLE);
        foreach ($translations as $translation) {
            $translate = new Translation();
            $translate->map($translation);
            $translate->insert();
        }
        CoreDB::getInstance()->commit();
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
