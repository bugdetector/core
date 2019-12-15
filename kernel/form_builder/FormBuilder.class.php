<?php

class FormBuilder extends FieldControl {
    const ENCRYPTION_METHOD = "aes128";
    private $method;
    private $fields = [];
    private $enctype = NULL;

    public function __construct(string $method = "GET", array $fields = [])
    {
        $this->method = $method;
        foreach($fields as $field){
            $this->addField($field);
        }
    }

    public function addField(FieldControl $field) : self{
        $this->fields[] = $field;
        return $this;
    }

    public function setEnctype(string $enctype) : self{
        $this->enctype = $enctype;
        return $this;
    }
    
    public function build(){
        return $this->renderField();
    }
    
    public function renderField(): string {
        return "<form method='$this->method' ".($this->enctype ? "enctype='$this->enctype'" : "").">".$this->renderFields()."</form>";
    }

    private function renderFields(){
        $render = "";
        foreach($this->fields as $field){
            $render.= $field->renderField("col-sm-4 col-md-3");
        }
        return $render;
    }
    
    public static function create_csrf(string $form_id, $value) {
        $encryption_key = bin2hex(random_bytes(10));
        $form_build_id = @openssl_encrypt($form_id, self::ENCRYPTION_METHOD,  $encryption_key);
        $_SESSION[$form_build_id] = [
            "encryption_key" => $encryption_key,
            "value" => $value
        ];
        return $form_build_id;
    }

    public static function get_csrf(string $form_build_id, string $form_id) {
        if(isset($_SESSION[$form_build_id])){
            $encryption_key = $_SESSION[$form_build_id]["encryption_key"];
            $value = $_SESSION[$form_build_id]["value"];

            $decrypted_form_id = openssl_decrypt($form_build_id, self::ENCRYPTION_METHOD, $encryption_key);
            if($form_id != $decrypted_form_id){
                throw new Exception(_t(95));
            }
            unset($_SESSION[$form_build_id]);
            return $value;
        }
    }

}