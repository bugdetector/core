<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\UnsignedBigInteger;
use CoreDB\Kernel\Model;
use PDO;

abstract class EntityTranslation extends Model
{
    private static array $staticCache = [];

    public UnsignedBigInteger $ID;
    public TableReference $object_id;
    public ShortText $locale;
    public ShortText $field;
    public LongText $content;

    public static function hasInCache($identifier, string $locale): bool
    {
        return isset(self::$staticCache[static::class][$identifier][$locale]);
    }

    public static function loadLocaleCache($identifier, string $locale): void
    {
        self::$staticCache[static::class][$identifier][$locale] = \CoreDB::database()
            ->select(static::getTableName(), "t")
            ->select("t", ["field", "content"])
            ->condition("object_id", $identifier)
            ->condition("locale", $locale)
            ->execute()
            ->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }

    public static function getFromCache($identifier, string $field, string $locale): ?string
    {
        if (!static::hasInCache($identifier, $locale)) {
            static::loadLocaleCache($identifier, $locale);
        }
        return self::$staticCache[static::class][$identifier][$locale][$field] ?? null;
    }

    public static function setToCache($identifier, string $field, string $locale, ?string $content): void
    {
        if (!static::hasInCache($identifier, $locale)) {
            static::loadLocaleCache($identifier, $locale);
        }
        self::$staticCache[static::class][$identifier][$locale][$field] = $content;
    }

    public static function clearCache($identifier = null): void
    {
        if ($identifier !== null) {
            unset(self::$staticCache[static::class][$identifier]);
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
