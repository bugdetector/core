<?php

namespace Src\Controller;

use CoreDB\Kernel\BaseController;
use Src\Entity\Translation;
use Src\Form\ProfileForm;

class ProfileController extends BaseController
{
    public ProfileForm $profileForm;

    public $activeTab = "profile";

    public function getTemplateFile(): string
    {
        return "page-profile.twig";
    }

    public function checkAccess(): bool
    {
        return \CoreDB::currentUser()->isLoggedIn();
    }

    public function preprocessPage()
    {
        $this->setTitle(
            Translation::getTranslation("profile")
        );
        $formClass = class_exists("App\Form\ProfileForm") ? "\App\Form\ProfileForm" : "\Src\Form\ProfileForm";
        $this->profileForm = new $formClass();
        $this->profileForm->processForm();
        $this->profileForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->profileForm;
    }
}
