<?php

$language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']) : "";
$language = strtoupper(preg_replace("/_.*/", "", $language));
Translator::$language = $language && in_array($language, Translator::get_available_language_list()) ? $language : strtoupper(LANGUAGE);
Translator::$cache = [];

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
    
    const BACKUP_PATH = __DIR__."/../translations/translations.json";

    public static function getTranslation(int $id){
        if(!isset(self::$cache[$id])){
            $obj = db_select(TRANSLATIONS)->select(TRANSLATIONS, [self::$language])->condition(" ID = :id", [":id" => $id])->limit(1)->execute()->fetch(PDO::FETCH_ASSOC);
            self::$cache[$id] = isset($obj[self::$language]) ? $obj[self::$language] : "";
        }
        return self::$cache[$id];
    }
    
    public static function getEmailTranslation(string $key){
        $obj = db_select(EMAILS)
                ->select(EMAILS, [self::$language])
                ->condition(" `KEY` = :key", [":key" => $key])
                ->limit(1)
                ->execute()->fetch(PDO::FETCH_ASSOC);
        return $obj[self::$language];
    }
    
    public static function get_available_language_list(){
        if(!isset(self::$available_languages)){
            $translation_table_description = get_table_description(TRANSLATIONS);
            unset($translation_table_description[0]);
            self::$available_languages = [];
            foreach ($translation_table_description as $column_description){
                self::$available_languages[] = $column_description[0];
            }
        }
        return self::$available_languages;
    }
}
