<?php
function send_result($result, $label = "msg") {
    echo json_encode([$label => $result]);
}

function throw_exception_as_json(string $msg) {
    http_response_code(400);
    die(json_encode(array('msg' => $msg)));   
}

abstract class ServicePage extends Page {

    function __construct(array $arguments) {
        parent::__construct($arguments);
    }
    
    abstract function callService(string $service_name);


    public function echoPage() {
        $this->preprocessPage();
    }


    public function preprocessPage(){
        $service_name = $this->arguments[1];
        try {
            if($service_name == "__construct"){
                throw new Exception(_t(67));
            }
            $this->callService($service_name);
        } catch (Exception $ex) {
            http_response_code(500);
            throw_exception_as_json($ex->getMessage());
        }
    }
    
    protected function echoContent() {
        
    }
}

