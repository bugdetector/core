<?php
/**
 * Management of Migrations
 *
 * @author murat
 */
namespace CoreDB\Kernel;

use CoreDB\Kernel\Database\QueryPreparer;
use Src\Entity\Cache;
use Src\Entity\Translation;
use Src\Entity\Variable;

class Migration
{
    const MIGRATIONS_DIR = __DIR__."/migrations";
    public static $version;

    public static function update()
    {
        $updates = self::getUpdates();
        $version = Variable::getByKey("version") ? : Variable::create("version");
        $new_version_number = null;
        foreach ($updates as $update) {
            include self::MIGRATIONS_DIR."/".$update;
            $new_version_number = (basename($update, ".php"));
            $version->value = $new_version_number;
            $version->save();
            self::$version = $new_version_number;
        }
        Cache::clear();
        Translation::importTranslations();
    }

    public static function getUpdates()
    {
        return array_filter(scandir(self::MIGRATIONS_DIR), function ($el) {
            if ($el == "." || $el == ".." || basename($el, ".php") <= (self::$version ? : VERSION)) {
                return false;
            } else {
                return true;
            }
        });
    }
    
    public static function addMigration(QueryPreparer $query)
    {
        $migration_file = self::MIGRATIONS_DIR."/".(doubleval(VERSION)+0.01).".php";
        if (!is_file($migration_file)) {
            $file = fopen($migration_file, "w");
            chmod($migration_file, 0777);
            fwrite($file, "<?php\n");
        } else {
            $file = fopen($migration_file, "a");
        }
        fwrite($file, "\CoreDB::database()->query(\"".$query->getQuery()."\"); \n");
    }
}
