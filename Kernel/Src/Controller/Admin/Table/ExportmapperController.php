<?php

namespace Src\Controller\Admin\Table;

use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\Router;
use Src\Controller\Admin\TableController;
use Src\Controller\NotFoundController;

class ExportmapperController extends TableController{

    public array $consts = [];
    public array $usedClasses = [];
    public array $fields = [];
    public array $fieldComments = [];
    public string $author;
    public function preprocessPage()
    {
        parent::preprocessPage();
        if($this->table_name){
            $tableDefiniton = TableDefinition::getDefinition($this->table_name);
            foreach($tableDefiniton->fields as $fieldName => $field){
                if(in_array($fieldName, ["ID", "created_at", "last_updated"])){
                    continue;
                }
                $this->usedClasses[] = get_class($field);
                $this->fields[$fieldName] = basename(str_replace('\\', '/', get_class($field)));
                $this->fieldComments[$fieldName] = $field->comment;
                if($field instanceof EnumaratedList){
                    foreach($field->values as $value){
                        $this->consts[ strtoupper("{$fieldName}_{$value}") ] = $value;
                    }
                }
            }
            $this->usedClasses = array_unique($this->usedClasses);
            $this->author = get_current_user();
        }else{
            Router::getInstance()->route(NotFoundController::getUrl());
        }
    }


    public function getTemplateFile(): string
    {
        return "page-export-mapper.twig";
    }

    public function echoContent()
    {
        var_dump($this->fields, JSON_PRETTY_PRINT);
    }
}