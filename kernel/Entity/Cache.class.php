<?php
/**
 * Object relation with table cache
 * @author murat
 */

class Cache extends DBObject{
    const TABLE = "cache";
    public $ID;
    public $bundle;
    public $key;
    public $value;
    public $created_at;
    public $last_updated;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @Override
     */
    public static function get(array $filter, string $table = self::TABLE){
        return parent::get($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter, string $table = self::TABLE) : array
    {
        return parent::getAll($filter, $table);
    }

    public static function set(string $bundle, string $key, string $value){
        $cache = Cache::get(["bundle" => $bundle, "key" => $key]) ? : new Cache();
        $cache->bundle = $bundle;
        $cache->key = $key;
        $cache->value = $value;
        $cache->save();
    }

    public static function getByBundleAndKey(string $bundle, string $key){
        return Cache::get(["bundle" => $bundle, "key" => $key]);
    }
}