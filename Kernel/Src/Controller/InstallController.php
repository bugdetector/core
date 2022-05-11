<?php

namespace Src\Controller;

use CoreDB\Kernel\Database\DatabaseInstallationException;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\InstallForm;
use CoreDB\Kernel\BaseController;

class InstallController extends BaseController
{
    public ?InstallForm $installForm = null;

    public function __construct($arguments)
    {
        $this->arguments = $arguments;
    }

    public function checkAccess(): bool
    {
        try {
            $hashSalt = Variable::getByKey("hash_salt");
            if (!defined("HASH_SALT") || !$hashSalt || $hashSalt->value->getValue() != HASH_SALT) {
                return !CONFIGURATON_LOADED;
            } else {
                return false;
            }
        } catch (DatabaseInstallationException $ex) {
            return true;
        }
    }


    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("install_welcome"));
        if (!@fopen("../config/config.php", "w+")) {
            $this->createMessage(Translation::getTranslation("config_file_write_error"));
        } else {
            if (is_file("../config/config.php")) {
                unlink("../config/config.php");
            }
            $this->installForm = new InstallForm();
            $this->installForm->processForm();
        }
    }

    public function getTemplateFile(): string
    {
        return "page-login.twig";
    }

    public function echoContent()
    {
        return $this->installForm;
    }
}
