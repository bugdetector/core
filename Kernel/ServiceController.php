<?php

namespace CoreDB\Kernel;

use Exception;
use Src\Entity\User;

abstract class ServiceController implements ControllerInterface
{

    const RESPONSE_TYPE_JSON = 0;
    const RESPONSE_TYPE_RAW = 1;
    public $arguments = [];
    public $messages = [];
    public $method;
    public $response_type = self::RESPONSE_TYPE_JSON;

    /**
     * ServiceController class construction
     * @param array $arguments
     *  Page arguments
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
        $this->method = isset($this->arguments[0]) ? $this->arguments[0] : null;
    }

    /**
     * @inheritdoc
     */
    public function checkAccess(): bool
    {
        return User::get_current_core_user()->isLoggedIn() && boolval($this->method);
    }

    /**
     * @inheritdoc
     */
    public function createMessage(string $message, int $type = Messenger::ERROR): void
    {
        if (!isset($this->messages[$type])) {
            $this->messages[$type] = [];
        }
        $this->messages[$type][] = $message;
    }

    public function processPage()
    {
        $response_data = ["data" => ""];
        try {
            $response = $this->{$this->method}();
            if($response){
                $response_data["data"] = $response;
            }
        } catch (Exception $ex) {
            http_response_code(400);
            $this->createMessage($ex->getMessage());
        }
        if($this->messages){
            $response_data["messages"] = $this->messages;
        }
        switch($this->response_type){
            case self::RESPONSE_TYPE_JSON:
                echo json_encode($response_data);
            break;
            case self::RESPONSE_TYPE_RAW:
                echo $response_data["data"];
            break;
        }        
    }
}