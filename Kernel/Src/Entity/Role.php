<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Model;

/**
 * Object relation with table roles
 * @author murat
 */

class Role extends Model
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
    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        unset($headers["ID"], $headers["created_at"], $headers["last_updated"]);
        return $headers;
    }
    /**
     * @inheritdoc
     */
    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $fields = parent::getSearchFormFields($translateLabel);
        unset($fields["ID"], $fields["created_at"], $fields["last_updated"]);
        return $fields;
    }
    /**
     * @inheritdoc
     */
    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()->select($this->getTableName(), "r")
        ->select("r", [
            "ID AS edit_actions",
            "role"
        ]);
    }
}
