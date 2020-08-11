<?php

namespace Src\Controller\Admin;

use CoreDB\Kernel\Router;
use Src\Controller\AdminController;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\UserInsertForm;
use Src\Theme\Views\CollapsableCard;

class UserController extends AdminController
{
    public UserInsertForm $form;
    public $user;
    public $role_options = [];
    public $operation;
    public $form_build_id;

    public function preprocessPage()
    {
        if (isset($this->arguments[0])) {
            $this->user = User::getUserByUsername($this->arguments[0]);
            if (!$this->user) {
                Router::getInstance()->route(Router::NOT_FOUND);
            }
            $this->setTitle(Translation::getTranslation("edit_user") . " | " . $this->user->username);
        } else if (isset($_GET["q"]) && $_GET["q"] == "insert") {
            $this->user = new User();
            $this->setTitle(Translation::getTranslation("add_user"));
        } else {
            $this->user = User::get_current_core_user();
            $this->setTitle(Translation::getTranslation("profile") . " | " . $this->user->username);
        }
        $this->form = new UserInsertForm($this->user);
        $this->form->processForm();
        /*
        if (isset($_POST["save"])) {
            if (!$this->checkCsrfToken(self::FORM_ID)) {
                $this->createMessage(Translation::getTranslation("invalid_operation"));
            } else {
                $user_info = $_POST["user_info"];
                $roles = isset($user_info["ROLES"]) ? $user_info["ROLES"] : [];
                unset($user_info["ID"], $user_info["password"], $user_info["ROLES"]);
                $success_message = $this->user->ID ? Translation::getTranslation("update_success") : Translation::getTranslation("insert_success");
                $send_email = !$this->user->ID && isset($user_info["email"]);
                try {
                    $this->user->map($user_info);
                    $this->user->save();
                    $this->user->updateRoles($roles);
                    if ($send_email) {
                        $reset_password = new ResetPassword();
                        $reset_password->user = $this->user->ID;
                        $reset_password->key = hash("SHA256", \CoreDB::get_current_date() . json_encode($this->user->ID));
                        $reset_password->save();
                        $reset_link = BASE_URL . "/reset_password/?USER=" . $this->user->ID . "&KEY=" . $reset_password->key;
                        $mail = _t_email("user_insert", [SITE_NAME, $this->user->username, $reset_link, $reset_link]);
                        \CoreDB::HTMLMail($this->user->email, Translation::getTranslation("user_definition", [SITE_NAME]), $mail, $this->user->getFullName());
                    }
                    $this->createMessage($success_message, "alert-success");
                    \CoreDB::goTo(BASE_URL . "/admin/user/{$this->user->username}");
                } catch (Exception $ex) {
                    $this->createMessage($ex->getMessage());
                }
            }
        } else if (isset($_POST["change_password"])) {
            if (!$this->checkCsrfToken(self::PASSWORD_FORM_ID)) {
                $this->createMessage(Translation::getTranslation("invalid_operation"));
            } else {
                try {
                    $password_info = $_POST["password"];
                    if (($this->user->ID == User::get_current_core_user()->ID && !password_verify($password_info["current_pass"], $this->user->password))
                        || $password_info["password"] != $password_info["password2"]
                    ) {
                        throw new Exception(Translation::getTranslation("password_be_sure_correct"));
                    } else {
                        $this->user->password = password_hash($password_info["password"], PASSWORD_BCRYPT);
                        $this->user->save();
                        $this->createMessage(Translation::getTranslation("update_success"), "alert-success");
                        \CoreDB::goTo(BASE_URL . "/admin/user/{$this->user->username}");
                    }
                } catch (Exception $ex) {
                    $this->createMessage($ex->getMessage());
                }
            }
        }
        $current_user_roles = $this->user->getUserRoles();
        $excluded_user_roles = array_diff(User::getAllAvailableUserRoles(), $current_user_roles);
        foreach ($current_user_roles as $role) {
            $this->role_options[] = (new OptionWidget($role, $role))->addAttribute("selected", "true");
        }
        foreach ($excluded_user_roles as $role) {
            $this->role_options[] = (new OptionWidget($role, $role));
        } */
    }

    public function getTemplateFile(): string
    {
        return "page-admin-user.twig";
    }

    public function echoContent()
    {
        return CollapsableCard::create($this->title)
        ->setId("user_edit")
        ->setContent($this->form);
        /*
        $password_entry = new FormBuilder("POST");
        $password_entry->addClass("col-12");
        if ($this->user->ID == User::get_current_core_user()->ID) {
            $password_entry->addField(
                InputField::create("password[current_pass]")
                    ->setLabel(Translation::getTranslation("current_pass"))
                    ->setType("password")
                    ->addAttribute("autocomplete", "off")
            );
        }
        $password_entry->addField(
            InputField::create("password[password]")
                ->setLabel(Translation::getTranslation("password"))
                ->setType("password")
                ->addAttribute("autocomplete", "new-password")
        )->addField(
            InputField::create("password[password2]")
                ->setLabel(Translation::getTranslation("password_again"))
                ->setType("password")
                ->addAttribute("autocomplete", "new-password")
        )->addField(
            InputField::create("change_password")
                ->setValue(Translation::getTranslation("update_password"))
                ->setType("submit")
                ->addClass("btn btn-outline-success")
        )->addField(
            InputField::create("form_build_id")->setValue($password_form_build_id)->setType("hidden")
        )->addField(
            InputField::create("form_token")->setValue($password_form_token)->setType("hidden")
        );

        $user_edit_form = $this->user->getForm("user_info");
        $user_edit_form->addField(
            InputField::create("form_build_id")->setValue($this->form_build_id)->setType("hidden")
        )->addField(
            InputField::create("form_token")->setValue($this->form_token)->setType("hidden")
        )->addField(
            SelectField::create("user_info[ROLES][]")
                ->addAttribute("multiple", "true")
                ->setOptions($this->role_options),
            1
        );

        $container = new Group("container-fluid");
        $container->addField(
            Group::create("d-sm-flex align-items-center justify-content-between mb-4")
                ->addField(
                    Group::create("h3 mb-0 text-gray-800")->setTagName("h1")
                        ->addField(TextElement::create($this->title))
                )
        )->addField(
            Group::create("row")
                ->addField(
                    Group::create("col-12")->addField($this)
                )
                ->addField(
                    Group::create("col-sm-6")->addField($user_edit_form)
                )->addField(
                    Group::create("col-sm-6")->addField($password_entry)
                )
        );
        echo $container; */
    }
}
