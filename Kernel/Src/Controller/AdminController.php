<?php

namespace Src\Controller;

use CoreDB;
use CoreDB\Kernel\BaseController;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;
use Src\Views\BasicCard;

class AdminController extends BaseController
{

    public $number_of_members;
    public $cards = [];
    
    public function checkAccess(): bool
    {
        return \CoreDB::currentUser()->isAdmin();
    }

    public function getTemplateFile(): string
    {
        return "page-admin.twig";
    }
    
    public function preprocessPage()
    {
        $this->setTitle(Variable::getByKey("site_name")->value . " | " . Translation::getTranslation("dashboard"));
        $this->number_of_members = CoreDB::database()->select(User::getTableName())
        ->selectWithFunction(["COUNT(*) as count"])
        ->execute()->fetchObject()->count;
        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-primary")
        ->setHref(BASE_URL . "/admin/entity/users")
        ->setTitle(Translation::getTranslation("number_of_members"))
        ->setDescription($this->number_of_members)
        ->setIconClass("fa-user")
        ->addClass("col-xl-3 col-md-6 mb-4");
        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-info")
        ->setHref(BASE_URL . "/admin/table")
        ->setTitle(Translation::getTranslation("table_count"))
        ->setDescription(count(\CoreDB::database()::getTableList()))
        ->setIconClass("fa-table")
        ->addClass("col-xl-3 col-md-6 mb-4");
        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-info")
        ->setHref(BASE_URL . "/admin/entity/translations")
        ->setTitle(Translation::getTranslation("translation_count"))
        ->setDescription(count(Translation::getAll([])))
        ->setIconClass("fa-language")
        ->addClass("col-xl-3 col-md-6 mb-4");
    }
    
    public function echoContent()
    {
    }
}
