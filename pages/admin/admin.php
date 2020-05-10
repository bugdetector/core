<?php

class AdminController extends Page {

    private $number_of_members;
    
    const admin_mainpage = "mainpage";
    
    public function check_access() : bool {
        return User::get_current_core_user()->isAdmin();
    }
    
    protected function preprocessPage() {
        $this->setTitle(SITE_NAME." | "._t("dashboard"));
        $this->number_of_members = db_select(User::TABLE)
        ->select_with_function(["COUNT(*) as count"])
        ->condition("USERNAME != :username", [":username" => "guest"])
        ->execute()->fetchObject()->count;
    }
    
    protected function echoContent() {
        $group = new Group("container-fluid");
        $group->addField(
            Group::create("d-sm-flex align-items-center justify-content-between mb-4")
                ->addField(
                    Group::create("h3 mb-0 text-gray-800")->setTagName("h1")
                    ->addField(TextElement::create(_t("dashboard")))
                )
            )->addField(
                Group::create("row")
                ->addField(
                    BasicCard::create("")
                    ->addClass("col-xl-3 col-md-6 mb-4")
                    ->setBorderClass("border-left-primary")
                    ->setHref(BASE_URL . "/admin/manage/user")
                    ->setTitle(_t("number_of_members"))
                    ->setDescription($this->number_of_members)
                    ->setIconClass("fa-user")
                )->addField(
                    BasicCard::create("")
                    ->addClass("col-xl-3 col-md-6 mb-4")
                    ->setBorderClass("border-left-info")
                    ->setHref(BASE_URL . "/admin/manage/update")
                    ->setTitle(_t("system_version"))
                    ->setDescription(VERSION)
                    ->setIconClass("fa-arrow-alt-circle-up")
                )
            );
        echo $group;
    }
}


