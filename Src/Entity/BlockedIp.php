<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table blocked_ips
 * @author murat
 */

class BlockedIp extends TableMapper
{
    public ShortText $ip;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "blocked_ips";
    }
}
