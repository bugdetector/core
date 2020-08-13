<?php

namespace Src\Controller\Admin\Manage;

use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\Migration;

use Exception;
use Src\Controller\Admin\ManageController;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Views\Table;
use Src\Views\ViewGroup;

/**
 * /admin/update
 *
 * @author murat
 */
class UpdateController extends ManageController
{

    public ViewGroup $content;
    public function checkAccess(): bool
    {
        if (!isset($_SESSION["install_key"])) {
            return parent::checkAccess();
        } else {
            return true;
        }
    }

    protected function addDefaultTranslations()
    {
        if (!isset($_SESSION["install_key"])) {
            parent::addDefaultTranslations();
        }
    }

    public function preprocessPage()
    {
        try {
            $title = Translation::getTranslation("updates");
            $success = Translation::getTranslation("update_success");
        } catch (Exception $ex) {
            $title = "Updates";
            $success = "Installed successfuly.";
        }
        $this->setTitle($title);
        $updates = Migration::getUpdates();
        if (isset($_POST["update"])) {
            Migration::update();
            $updates = Migration::getUpdates();
            \CoreDB::messenger()->createMessage($success, Messenger::SUCCESS);
            if (isset($_SESSION["install_key"])) {
                unset($_SESSION["install_key"]);
                \CoreDB::goTo(BASE_URL . "/admin/manage/update");
            }
        }
        $table_headers = ["$title"];
        $table_content = array_map(function ($el) {
            return [basename($el, ".php")];
        }, $updates);
        
        $this->content = ViewGroup::create("div", "")
        ->addField(
            new Table($table_headers, $table_content)
        )->addField(
            $this->getForm()
        );

        parent::preprocessPage();
    }

    public function echoContent()
    {
        return $this->content;
    }

    public function getForm()
    {
        $form = new ViewGroup("form","post");
        try {
            $no_update = Translation::getTranslation("no_update");
            $available_version = Translation::getTranslation("available_version");
        } catch (Exception $ex) {
            $no_update = "There is no update.";
            $available_version = "System will update to version";
        }
        if (empty($this->updates)) {
            $input = new InputWidget("ok");
            $input->setType("submit")->setLabel($no_update)->addClass("d-none");
            $form->addField($input);
        } else {
            $input = new InputWidget("update");
            $input->setType("submit")
                ->addClass("btn btn-sm btn-primary shadow-sm")
                ->setValue(VERSION ? "Update" : "Install " . SITE_NAME);
            if (!VERSION) {
                $form->addClass("container justify-content-center align-items-center");
                $input->setLabel("$available_version: " . basename(max($this->updates), ".php"));
            } else {
                $input->setLabel("$available_version: " . basename(max($this->updates), ".php"));
            }
            $form->addField($input);
        }
        return $form;
    }
}
