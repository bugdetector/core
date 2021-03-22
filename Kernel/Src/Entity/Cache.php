<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Model;

/**
 * Object relation with table cache
 * @author murat
 */

class Cache extends Model
{
    public ShortText $bundle;
    public ShortText $key;
    public LongText $value;

    private static array $staticCache = [];
    public function __construct(string $tableName = null, array $mapData = [])
    {
        $this->ID = new Integer("");
        $this->bundle = new ShortText("");
        $this->key = new ShortText("");
        $this->value = new LongText("");
        $this->created_at = new DateTime("");
        $this->last_updated = new DateTime("");
        $this->map($mapData);
    }

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "cache";
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
