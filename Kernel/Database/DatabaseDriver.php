<?php

namespace CoreDB\Kernel\Database;

use CoreDB\Kernel\Database\DatabaseDriverInterface;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\Date;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\DataType\Time;

abstract class DatabaseDriver implements DatabaseDriverInterface
{

    public static function dataTypes(): array
    {
        return [
            self::INTEGER => Integer::class,
            self::FLOAT => FloatNumber::class,
            self::CHECKBOX => Checkbox::class,
            self::SHORT_TEXT => ShortText::class,
            self::TEXT => Text::class,
            self::LONG_TEXT => LongText::class,
            self::DATE => Date::class,
            self::DATETIME => DateTime::class,
            self::TIME => Time::class,
            self::FILE => File::class,
            self::TABLE_REFERENCE => TableReference::class,
            self::ENUMARATED_LIST => EnumaratedList::class
        ];
    }
}
