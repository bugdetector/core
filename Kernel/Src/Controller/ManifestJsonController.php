<?php

namespace Src\Controller;

use CoreDB\Kernel\ServiceController;

class ManifestJsonController extends ServiceController
{
    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        if (!$this->method) {
            $this->method = "getManifestFile";
        }
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function getManifestFile()
    {
        $this->response_type = self::RESPONSE_TYPE_RAW;
        return json_encode(PWA_MANIFEST);
    }
}
