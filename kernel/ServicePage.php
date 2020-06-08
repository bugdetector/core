<?php

abstract class ServicePage extends Page {

    function __construct(array $arguments) {
        parent::__construct($arguments);
    }
    
    protected function send_result($result, $label = "msg") {
        echo json_encode([$label => $result]);
    }

    protected function throw_exception_as_json(string $msg) {
        http_response_code(400);
        die(json_encode(array('msg' => $msg)));   
    }
    
    abstract function callService(string $service_name);


    public function echoPage() {
        $this->preprocessPage();
    }


    public function preprocessPage(){
        $service_name = $this->arguments[0];
        try {
            if($service_name == "__construct"){
                throw new Exception(_t(67));
            }
            $this->callService($service_name);
        } catch (Exception $ex) {
            http_response_code(500);
            $this->throw_exception_as_json($ex->getMessage());
        }
    }
    
    protected function echoContent() {
        
    }
}
