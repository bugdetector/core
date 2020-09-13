<?php

namespace CoreDB\Kernel;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use PDO;
use Src\Entity\DBObject;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\OptionWidget;
use Src\Form\Widget\SelectWidget;

class EntityReference extends DataTypeAbstract
{

    const CONNECTION_MANY_TO_MANY = "manyToMany";
    const CONNECTION_MANY_TO_ONE = "manyToOne";
    const CONNECTION_ONE_TO_MANY = "oneToMany";
    const CONNECTION_ONE_TO_ONE = "oneToOne";

    public TableMapper $object;
    public string $fieldEntityName;
    public string $connectionType;
    public string $mergeTable;
    public string $selfKey;
    public string $foreignKey;

    public function __construct(string $fieldEntityName, TableMapper &$object, array $config, string $connectionType)
    {
        $this->fieldEntityName = $fieldEntityName;
        $this->object = $object;
        $this->connectionType = $connectionType;
        if ($connectionType == self::CONNECTION_MANY_TO_MANY) {
            $this->mergeTable = $config["mergeTable"];
            $this->selfKey = $config["selfKey"];
            $this->foreignKey = $config["foreignKey"];
        }
    }

    /**
     * Pseudo function. There is no use.
     * @inheritdoc
     */
    public static function getText(): string{return "";}

    public function getWidget(): FormWidget
    {
        $widget = null;
        if ($this->connectionType == self::CONNECTION_MANY_TO_MANY) {
            $checkeds = $this->getCheckeds();

            $referenceClass = \CoreDB::config()->getEntityInfo($this->fieldEntityName)["class"];
            $allOptions = \CoreDB::database()->select($referenceClass::getTableName())
                ->select("", ["ID"])
                ->limit(100)
                ->execute()->fetchAll(PDO::FETCH_COLUMN);
            $allOptions = array_unique(array_merge($checkeds, $allOptions));

            $options = [];
            foreach ($allOptions as $anOption) {
                $objectArray = $referenceClass::get(["ID" => $anOption])->toArray();
                $text = current($objectArray);
                $option = new OptionWidget($anOption, $text);
                if (in_array($anOption, $checkeds)) {
                    $option->setSelected(true);
                }
                $options[] = $option;
            }
            $widget = SelectWidget::create("")
            ->addAttribute("multiple", "true")
            ->setOptions($options);
        }
        return $widget;
    }

    public function getSearchWidget(): FormWidget
    {
        return $this->getWidget();
    }

    private function getCheckeds() : array{
        return \CoreDB::database()->select($this->mergeTable)
                ->select("", [$this->foreignKey])
                ->condition($this->selfKey, $this->object->ID->getValue())
                ->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    public function save(){
        if($this->connectionType = self::CONNECTION_MANY_TO_MANY){
            if(!$this->value){
                $this->value = [];
            }
            $checkeds = $this->getCheckeds();
            foreach($this->value as $index => $value){
                if(isset($checkeds[$index])){
                    $object = DBObject::get([
                        $this->selfKey => $this->object->ID->getValue(),
                        $this->foreignKey => $checkeds[$index]
                    ], $this->mergeTable);
                }else{
                    $object = new DBObject($this->mergeTable);
                }
                $object->map([
                    $this->selfKey => $this->object->ID->getValue(),
                    $this->foreignKey => $value
                ]);
                $object->save();
            }
            if(isset($index)){
                $index++;
            }else{
                $index = 0;
            }
            if(isset($checkeds[$index])){
                for($index; $index < count($checkeds); $index++){
                    $object = DBObject::get([
                        $this->selfKey => $this->object->ID->getValue(),
                        $this->foreignKey => $checkeds[$index]
                    ], $this->mergeTable);
                    $object->delete();
                }
            }
        }
    }
}
