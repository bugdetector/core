<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table cache
 * @author murat
 */

class Cache extends TableMapper
{
    const TABLE = "cache";
    public ShortText $bundle;
    public ShortText $key;
    public LongText $value;

    private static array $staticCache = [];
    public function __construct()
    {
        $this->table = self::TABLE;
        $this->ID = new Integer("");
        $this->bundle = new ShortText("");
        $this->key = new ShortText("");
        $this->value = new LongText("");
        $this->created_at = new DateTime("");
        $this->last_updated = new DateTime("");
    }

    /**
     * @inheritdoc
     */
    public static function get(array $filter): ?Cache
    {
        return parent::find($filter, self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function getAll(array $filter): array
    {
        return parent::findAll($filter, self::TABLE);
    }

    public static function clear()
    {
        parent::clearTable(self::TABLE);
    }

    public static function set(string $bundle, string $key, string $value)
    {
        if ($bundle && $key && $value) {
            $cache = Cache::get(["bundle" => $bundle, "key" => $key]) ?: new Cache();
            $cache->bundle->setValue($bundle);
            $cache->key->setValue($key);
            $cache->value->setValue($value);
            $cache->save();
            self::$staticCache[$bundle][$key] = $cache;
        }
    }

    public static function getByBundleAndKey(string $bundle, string $key): ?Cache
    {
        if (!isset(self::$staticCache[$bundle][$key])) {
            self::$staticCache[$bundle][$key] = Cache::get(["bundle" => $bundle, "key" => $key]);
        }
        return self::$staticCache[$bundle][$key];
    }
}
