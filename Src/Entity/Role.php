<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\TableMapper;

/**
 * Object relation with table roles
 * @author murat
 */

class Role extends TableMapper
{
    public ShortText $role;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "roles";
    }

    /**
     * @inheritdoc
     */
    public function getTableHeaders(bool $translateLabel = true) : array{
        $headers = parent::getTableHeaders($translateLabel);
        unset($headers["ID"], $headers["created_at"], $headers["last_updated"]);
        return $headers;
    }
    /**
     * @inheritdoc
     */
    public function getSearchFormFields(bool $translateLabel = true) : array{
        $fields = parent::getSearchFormFields($translateLabel);
        unset($fields["ID"], $fields["created_at"], $fields["last_updated"]);
        return $fields;
    }
    /**
     * @inheritdoc
     */
    public function getTableQuery() : SelectQueryPreparerAbstract{
        return \CoreDB::database()->select($this->getTableName(), "r")
        ->select("r", [
            "ID AS edit_actions",
            "role"
        ]);
    }
}
