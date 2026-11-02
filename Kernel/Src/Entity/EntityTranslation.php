<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\UnsignedBigInteger;
use CoreDB\Kernel\Model;

abstract class EntityTranslation extends Model
{
    private static array $staticCache = [];

    public UnsignedBigInteger $ID;
    public TableReference $object_id;
    public ShortText $locale;
    public ShortText $field;
    public LongText $content;

    public static function hasInCache($objectId, string $field, string $locale): bool
    {
        return isset(self::$staticCache[static::class][$objectId][$locale])
            && array_key_exists($field, self::$staticCache[static::class][$objectId][$locale]);
    }

    public static function getFromCache($objectId, string $field, string $locale): ?string
    {
        return self::$staticCache[static::class][$objectId][$locale][$field] ?? null;
    }

    public static function setToCache($objectId, string $field, string $locale, ?string $content): void
    {
        self::$staticCache[static::class][$objectId][$locale][$field] = $content;
    }

    public static function clearCache($objectId = null): void
    {
        if ($objectId !== null) {
            unset(self::$staticCache[static::class][$objectId]);
        } else {
            unset(self::$staticCache[static::class]);
        }
    }

    public static function clear()
    {
        static::clearCache();
        return parent::clear();
    }
}
