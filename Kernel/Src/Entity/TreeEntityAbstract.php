<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\TableMapper;
use Exception;

abstract class TreeEntityAbstract extends TableMapper
{
    public Integer $weight;

    abstract public static function getTreeFieldName(): string;

    abstract public function getRemoveServicecUrl(): string;

    public static function getRootElements(): array
    {
        if (static::hasSubItems()) {
            return static::findAll(["parent" => null], static::getTableName(), "weight");
        } else {
            return static::findAll([], static::getTableName(), "weight");
        }
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
