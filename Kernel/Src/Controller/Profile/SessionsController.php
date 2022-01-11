<?php

namespace Src\Controller\Profile;

use Src\Controller\ProfileController;
use Src\Entity\Translation;
use Src\Form\SessionsForm;

class SessionsController extends ProfileController
{
    public $activeTab = "sessions";
    public SessionsForm $sessionsForm;

    public function preprocessPage()
    {
        $this->setTitle(
            Translation::getTranslation("sessions")
        );
        $formClass = class_exists("App\Form\SessionsForm") ? "\App\Form\SessionsForm" : "\Src\Form\SessionsForm";
        $this->sessionsForm = new $formClass();
        $this->sessionsForm->processForm();
        $this->sessionsForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->sessionsForm;
    }
}
