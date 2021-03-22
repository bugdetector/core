<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Model;

/**
 * Object relation with table blocked_ips
 * @author murat
 */

class BlockedIp extends Model
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
