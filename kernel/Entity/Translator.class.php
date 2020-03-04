<?php

/**
 * 
 * @param int $id
 * @param array $arguments
 * @return string
 */
function _t(int $id, array $arguments = NULL) {
    $translation = Translator::getTranslation($id);
    if($arguments){
        $translation = vsprintf($translation, $arguments);
    }
    return $translation;
}

function _t_email(string $key, array $arguments = NULL) {
    $translation = htmlspecialchars_decode(Translator::getEmailTranslation($key));
    $translation = str_replace("http://%s", "%s", $translation);
    if($arguments){
        $translation = vsprintf($translation, $arguments);
    }
    return $translation;
}


class Translator {
    static $language;
    static $cache;
    static $available_languages;
    
    const BACKUP_PATH = __DIR__."/../../translations/translations.json";

    public static function getLanguage()
    {
        if(!Translator::$language){
            $language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']) : "";
            $language = strtoupper(preg_replace("/_.*/", "", $language));
            Translator::$language = $language && in_array($language, Translator::get_available_language_list()) ? $language : strtoupper(LANGUAGE);
            Translator::$cache = [];
            foreach(json_decode(file_get_contents(Translator::BACKUP_PATH)) as $translation){
                Translator::$cache[$translation->ID] = $translation->{Translator::$language};
            }
        }
        return self::$language;
    }

    public static function getTranslation(int $id){
        if(!isset(self::$cache[$id])){
            $obj = db_select(TRANSLATIONS)->select(TRANSLATIONS, [self::getLanguage()])->condition(" ID = :id", [":id" => $id])->limit(1)->execute()->fetch(PDO::FETCH_ASSOC);
            self::$cache[$id] = isset($obj[self::getLanguage()]) ? $obj[self::getLanguage()] : "";
        }
        return self::$cache[$id];
    }
    
    public static function getEmailTranslation(string $key){
        $obj = db_select(EMAILS)
                ->select(EMAILS, [self::getLanguage()])
                ->condition(" `KEY` = :key", [":key" => $key])
                ->limit(1)
                ->execute()->fetch(PDO::FETCH_ASSOC);
        return $obj[self::getLanguage()];
    }
    
    public static function get_available_language_list(){
        if(!isset(self::$available_languages)){
            $translation_table_description = CoreDB::get_table_description(TRANSLATIONS, false);
            unset($translation_table_description[0]);
            self::$available_languages = [];
            foreach ($translation_table_description as $column_description){
                self::$available_languages[] = $column_description[0];
            }
        }
        return self::$available_languages;
    }

    public static function import_translations(){
        $translations = json_decode(file_get_contents(Translator::BACKUP_PATH));
        CoreDB::getInstance()->beginTransaction();
        db_truncate(TRANSLATIONS);
        foreach ($translations as $translation){
                db_insert(TRANSLATIONS, (array) $translation)->execute();
        }
        CoreDB::getInstance()->commit();
    }

    public static function export_translations(){
        $translations = db_select(TRANSLATIONS)->execute()->fetchAll(PDO::FETCH_ASSOC);
        if(file_exists(Translator::BACKUP_PATH)){
            unlink(Translator::BACKUP_PATH);
        }
        file_put_contents(Translator::BACKUP_PATH, json_encode($translations, JSON_PRETTY_PRINT));
    }
}
