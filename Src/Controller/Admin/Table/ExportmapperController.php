<?php

namespace Src\Controller\Admin\Table;

use CoreDB\Kernel\Database\TableDefinition;
use CoreDB\Kernel\Router;
use Src\Controller\Admin\TableController;
use Src\Controller\NotFoundController;

class ExportmapperController extends TableController{

    public array $usedClasses = [];
    public array $fields = [];
    public string $author;
    public function preprocessPage()
    {
        parent::preprocessPage();
        if($this->table_name){
            $tableDefiniton = TableDefinition::getDefinition($this->table_name);
            foreach($tableDefiniton->fields as $fieldName => $field){
                $this->usedClasses[] = get_class($field);
                $this->fields[$fieldName] = basename(str_replace('\\', '/', get_class($field)));
            }
            unset($this->fields["ID"], $this->fields["created_at"], $this->fields["last_updated"]);
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