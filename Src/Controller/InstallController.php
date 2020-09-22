<?php

namespace Src\Controller;

use CoreDB\Kernel\Messenger;
use Src\Entity\Translation;
use Src\Form\InstallForm;
use Src\Theme\BaseTheme\BaseTheme;

class InstallController extends BaseTheme {

    public InstallForm $installForm;

    public function __construct($arguments)
    {
        parent::__construct($arguments);
    }

    public function processPage(){
        $this->addDefaultJsFiles();
        $this->addDefaultCssFiles();
        $this->preprocessPage();
        $this->render();
    }


    public function preprocessPage()
    {
        $this->body_classes[] = "bg-gradient-info";
        $this->setTitle(Translation::getTranslation("install_welcome"));
        $this->installForm = new InstallForm();
        $this->installForm->processForm();
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