<?php

class AdminTableDbobjectController extends AdminTableController{
    protected $author, $class_name, $table, $fields;

    public function check_access() : bool {
        return parent::check_access() && isset($this->arguments[0]) && in_array($this->arguments[0], CoreDB::get_information_scheme());
    }
    
    protected function preprocessPage() {
        parent::preprocessPage();
        $this->table = $this->arguments[0];
        $this->class_name = str_replace(" ", "", mb_convert_case(preg_replace("/([^A-Za-z])/", " ", $this->table), MB_CASE_TITLE));
        $this->author = get_current_user();
        $this->fields = "";
        $fields = [];
        foreach (CoreDB::get_table_description($this->table) as $col){
            $fields[] = "public $$col[0]";
        }
        $this->fields = implode($fields, ";\n    ");
    }
    
    protected function echoContent() {
        $example = str_replace(
                    ["{{author}}", "{{class_name}}","{{table_name}}", "{{fields}}"],
                    [$this->author, $this->class_name,$this->table, $this->fields], 
                    file_get_contents("DBObject.example", TRUE)
                );
        $wrapper = new Group("col-sm-12");
        $text_field = new TextareaField("");
        $text_field->setLabel("{$this->table} DBObject:")->setValue(htmlspecialchars($example))
                ->addAttribute("rows", substr_count($example, "\n" ) + 1);
        echo $wrapper->addField($text_field);
    }
}
