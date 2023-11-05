<?php

namespace CoreDB\Kernel\Database\DataType;

use Src\Entity\Translation;

class UnsignedBigInteger extends Integer
{
    /**
     * @inheritdoc
     */
    public static function getText(): string
    {
        return Translation::getTranslation("big_integer");
    }
}
