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
        $version = Variable::getByKey("version") ? : new Variable("version");
        $new_version_number = NULL;
        foreach ($updates as $update) {
            include self::MIGRATIONS_DIR."/".$update;
            $new_version_number = (basename($update, ".php"));
            $version->value = $new_version_number;
            $version->save();
            self::$version = $new_version_number;
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
