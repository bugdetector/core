<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\Model;
use Exception;
use Src\Theme\View;

/**
 * Object relation with table variables
 * @author murat
 */

class Variable extends Model
{
    public ShortText $key;
    public LongText $value;
    public EnumaratedList $type;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "variables";
    }

    public static function create($key): Variable
    {
        $variable = new Variable();
        $variable->key->setValue($key);
        return $variable;
    }

    public static function getByKey(string $key): ?Variable
    {
        return self::get(["key" => $key]);
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = [""];
        $fields = [
            "key",
            "value",
            "type"
        ];
        foreach ($fields as $header) {
            $headers[$header] = $translateLabel ? Translation::getTranslation($header) : $header;
        }
        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()
        ->select(Variable::getTableName(), "v")
        ->select("v", ["ID AS edit_actions" ,"key", "value", "type"])
        ->condition("type", "hidden", "!=");
    }

    public function postProcessRow(&$row): void
    {
        parent::postProcessRow($row);
        $row["type"] = Translation::getTranslation($row["type"]);
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "value") {
            if (!$this->type->getValue()) {
                return null;
            } else {
                $type = $this->type->getValue();
                if ($type == "hidden") {
                    $type = "text";
                }
                $dataTypes = \CoreDB::database()->dataTypes();
                if (isset($dataTypes[$type])) {
                    /**
                     * @var DataTypeAbstract
                     */
                    $dataType = new $dataTypes[$type]($field_name);
                    $dataType->setValue($this->value->getValue());
                    $dataType->comment = $this->value->comment;
                    $widget = $dataType->getWidget();
                    $widget->setLabel($translateLabel ? Translation::getTranslation("value") : "value");
                    return $widget;
                }
            }
        }
        return parent::getFieldWidget($field_name, $translateLabel);
    }
}
