<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Model;
use Exception;

abstract class TreeEntityAbstract extends Model
{
    public Integer $weight;

    abstract public static function getTreeFieldName(): string;

    abstract public function getRemoveServiceUrl(): string;

    public static function getRootElements(): array
    {
        if (static::hasSubItems()) {
            return static::findAll(["parent" => null], static::getTableName(), "weight");
        } else {
            return static::findAll([], static::getTableName(), "weight");
        }
    }

    protected function insert()
    {
        if (!$this->weight->getValue()) {
            $this->weight->setValue(
                \CoreDB::database()->select($this->getTableName())
                    ->selectWithFunction([
                        "MAX(weight)"
                    ])->execute()->fetchColumn() + 1
            );
        }
        return parent::insert();
    }

    public static function hasSubItems()
    {
        return true;
    }

    public function getSubNodes(): array
    {
        return static::findAll(["parent" => $this->ID], static::getTableName(), "weight");
    }

    public function delete(): bool
    {
        try {
            if ($this->hasSubItems()) {
                foreach ($this->getSubNodes() as $subNode) {
                    $subNode->delete();
                }
            }
            return parent::delete();
        } catch (Exception $ex) {
            return false;
        }
    }
}
