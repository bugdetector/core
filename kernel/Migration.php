<?php
/**
 * Management of Migrations
 *
 * @author murat
 */
class Migration {
    const MIGRATIONS_DIR = "./kernel/migrations";
    public static $version;

    public static function update(){
        $updates = self::getUpdates();
        $config = file_get_contents(".config.php");
        $new_version_number = NULL;
        try{
            CoreDB::getInstance()->beginTransaction();
            foreach ($updates as $update) {
                include self::MIGRATIONS_DIR."/".$update;
                $new_version_number = (basename($update, ".php"));
                $new_config = str_replace('define("VERSION", "'.VERSION.'");', 'define("VERSION", "'.$new_version_number.'");', $config);
                file_put_contents(".config.php", $new_config);
                self::$version = $new_version_number;
                CoreDB::getInstance()->commit();
            }
            if(!class_exists("Translator")){
                Utils::include_dir("Entity");
            }
            Translator::import_translations();
        }catch(Exception $ex){
            CoreDB::getInstance()->rollback();
            throw $ex;
        }
    }

    public static function getUpdates() {
        return array_filter(scandir(self::MIGRATIONS_DIR), function($el){
            if($el == "." || $el == ".." || basename($el, ".php") <= (self::$version ? : VERSION)){
                return FALSE;
            } else {
                return TRUE;
            }
        });
    }
    
    public static function addMigration(CoreDBQueryPreparer $query){
        $migration_file = self::MIGRATIONS_DIR."/".(doubleval(VERSION)+0.01).".php";
        if(!is_file($migration_file)){
            $file = fopen($migration_file, "w");
            chmod($migration_file, 0777);
            fwrite($file, "<?php\n");
        } else {
            $file = fopen($migration_file, "a");
        }
        fwrite($file,"db_query(\"".$query->getQuery()."\"); \n");
    }
}
