<?php

namespace Src\Entity;

use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table cache
 * @author murat
 */

class Cache extends TableMapper
{
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
    public static function get(array $filter) : ?Cache
    {
        return parent::find($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter) : array
    {
        return parent::findAll($filter, self::TABLE);
    }

    public static function clear()
    {
        parent::clearTable(self::TABLE);
    }

    public static function set(string $bundle, string $key, string $value)
    {
        $cache = Cache::get(["bundle" => $bundle, "key" => $key]) ? : new Cache();
        $cache->bundle = $bundle;
        $cache->key = $key;
        $cache->value = $value;
        $cache->save();
    }

    public static function getByBundleAndKey(string $bundle, string $key) : ?Cache
    {
        return Cache::get(["bundle" => $bundle, "key" => $key]);
    }
}
