<?php

class AdminTableInsertController extends AdminTableController
{

    const FORM_ID = "insert_form";

    public $object = NULL;
    public $table;

    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        if (!$this->arguments) {
            Router::getInstance()->loadPage(Router::$notFound);
        }
    }

    protected function preprocessPage()
    {
        if (isset($this->arguments[0]) && !in_array($this->arguments[0], CoreDB::get_information_scheme())) {
            Router::getInstance()->route(Router::$notFound);
        }
        $this->table = $this->arguments[0];
        if (isset($this->arguments[1]) && !isset($_POST["insert?"])) {
            $this->object = DBObject::get(["ID" => $this->arguments[1]], $this->table);
            if (!$this->object) {
                Router::getInstance()->route(Router::$notFound);
            }
            $this->setTitle(_t("edit") . " | " . $this->table . " ID: {$this->object->ID}");
        } else if (!isset($_POST["delete?"])) {
            $this->object = new DBObject($this->table);
            $this->setTitle(_t("add") . " | " . $this->table);
        } else {
            Router::getInstance()->route(Router::$accessDenied);
        }

        if (isset($_POST["insert?"]) || isset($_POST["update?"])) {
            if ($this->checkCsrfToken(self::FORM_ID)) {
                try {
                    $this->object->map($_POST["object"]);
                    $success_message = $this->object->ID ? _t("update_success") : _t("insert_success");
                    $this->object->save();
                    unset($_FILES["files"]); //Summernote uses files index
                    if (!empty($_FILES)) {
                        $this->object->include_files($_FILES["object"]);
                    }
                    $this->create_warning_message($success_message, "alert-success");
                    Utils::core_go_to(BASE_URL . "/admin/table/insert/{$this->table}/" . $this->object->ID);
                } catch (PDOException $ex) {
                    $this->create_warning_message($ex->getMessage());
                }
            } else {
                $this->create_warning_message(_t("invalid_operation"));
            }
        } else if (isset($_POST["delete?"]) && $this->object) {
            if ($this->checkCsrfToken(self::FORM_ID)) {
                try {
                    $this->object->delete();
                    $this->create_warning_message(_t("record_removed"), "alert-success");
                    Utils::core_go_to(BASE_URL . "/admin/table/{$this->table}");
                } catch (PDOException $ex) {
                    $this->create_warning_message($ex->getMessage());
                }
            } else {
                $this->create_warning_message(_t("invalid_operation"));
            }
        }

        $this->form_build_id = $this->createCsrf(self::FORM_ID);
        $this->form_token = $this->createFormToken($this->form_build_id);
        $this->add_js_files("pages/admin/table/insert/insert.js");
    }

    protected function echoContent()
    {
        $form = $this->object->getForm("object");

        $form->addField(
            (new InputField("form_build_id"))
                ->setValue($this->form_build_id)
                ->setType("hidden")
        )->addField(
            (new InputField("form_token"))
                ->setValue($this->form_token)
                ->setType("hidden")
        );

        $group = new Group("container-fluid");
        $group->addField(
            Group::create("d-sm-flex align-items-center justify-content-between mb-4")
                ->addField(
                    Group::create("h3 mb-0 text-gray-800")->setTagName("h1")
                        ->addField(TextElement::create($this->title))
                )
        )->addField(
            Group::create("btn btn-outline-info mt-4 mb-4")->setTagName("a")
                ->addAttribute("href", BASE_URL . "/admin/table/{$this->table}")
                ->addField(
                    Group::create("fa fa-chevron-left")->setTagName("i")
                )->addField(
                    TextElement::create(" " . _t("back_table"))
                )
        )->addField($this)->addField($form);
        echo $group;
    }

    protected function add_default_translations()
    {
        parent::add_default_translations();
        $this->add_frontend_translation("record_remove_accept");
    }
}
