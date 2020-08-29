<?php

namespace Src\Entity;

use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table emails
 * @author murat
 */

class Email extends TableMapper
{
    const TABLE = "emails";
    public $ID;
    public $key;
    public $en;
    public $tr;
    public $created_at;
    public $last_updated;

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function get(array $filter) : ?Email
    {
        return parent::find($filter, self::TABLE);
    }

    /**
     * @inheritdoc
     */
    public static function getAll(array $filter) : array
    {
        return parent::findAll($filter, self::TABLE);
    }

    public static function clear()
    {
        parent::clearTable(self::TABLE);
    }
}
