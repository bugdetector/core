<?php

namespace Src\Controller;

use Src\Theme\BaseTheme\BaseTheme;
use CoreDB\Kernel\Database\CoreDB;
use CoreDB\Kernel\Database\SelectQueryPreparer;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Theme\Views\BasicCard;

class AdminController extends BaseTheme {

    public $number_of_members;
    public $cards = [];
    
    public function checkAccess() : bool {
        return User::get_current_core_user()->isAdmin();
    }

    public function getTemplateFile(): string
    {
        return "page-admin.twig";
    }
    
    public function preprocessPage() {
        $this->setTitle(SITE_NAME." | ".Translation::getTranslation("dashboard"));
        $this->number_of_members = (new SelectQueryPreparer(User::TABLE))
        ->select_with_function(["COUNT(*) as count"])
        ->condition("USERNAME != :username", [":username" => "guest"])
        ->execute()->fetchObject()->count;
        $this->cards[] = BasicCard::create()
        ->addClass("col-xl-3 col-md-6 mb-4")
        ->setBorderClass("border-left-primary")
        ->setHref(BASE_URL . "/admin/manage/user")
        ->setTitle(Translation::getTranslation("number_of_members"))
        ->setDescription($this->number_of_members)
        ->setIconClass("fa-user");
        $this->cards[] = BasicCard::create()
        ->addClass("col-xl-3 col-md-6 mb-4")
        ->setBorderClass("border-left-info")
        ->setHref(BASE_URL . "/admin/manage/update")
        ->setTitle(Translation::getTranslation("system_version"))
        ->setDescription(VERSION)
        ->setIconClass("fa-arrow-alt-circle-up");
        $this->cards[] = BasicCard::create()
        ->addClass("col-xl-3 col-md-6 mb-4")
        ->setBorderClass("border-left-info")
        ->setHref(BASE_URL . "/admin/table")
        ->setTitle(Translation::getTranslation("table_count"))
        ->setDescription(count(\CoreDB::database()::getTableList()))
        ->setIconClass("fa-table");
        $this->cards[] = BasicCard::create()
        ->addClass("col-xl-3 col-md-6 mb-4")
        ->setBorderClass("border-left-info")
        ->setHref(BASE_URL . "/admin/manage/translation")
        ->setTitle(Translation::getTranslation("translation_count"))
        ->setDescription(count(Translation::getAll([])))
        ->setIconClass("fa-language");
    }
    
    public function echoContent() {}
}


