<?php

/**
 * @property User $user
 */
class AdminUserController extends AdminController
{
    const FORM_ID = "admin_user_edit_form";
    const PASSWORD_FORM_ID = "admin_user_change_password";
    public $user;
    public $role_options = [];
    public $operation;
    public $form_build_id;

    protected function preprocessPage()
    {
        parent::preprocessPage();
        if (isset($this->arguments[0])) {
            $this->user = User::getUserByUsername($this->arguments[0]);
            if (!$this->user) {
                Router::getInstance()->route(Router::$notFound);
            }
            $this->setTitle(_t("edit_user") . " | " . $this->user->username);
        } else if (isset($_GET["q"]) && $_GET["q"] == "insert") {
            $this->user = new User();
            $this->setTitle(_t("add_user"));
        } else {
            $this->user = User::get_current_core_user();
            $this->setTitle(_t("profile") . " | " . $this->user->username);
        }
        if (isset($_POST["save"])) {
            if (!$this->checkCsrfToken(self::FORM_ID)) {
                $this->create_warning_message(_t("invalid_operation"));
            } else {
                $user_info = $_POST["user_info"];
                $roles = isset($user_info["ROLES"]) ? $user_info["ROLES"] : [];
                unset($user_info["ID"], $user_info["password"], $user_info["ROLES"]);
                $success_message = $this->user->ID ? _t("update_success") : _t("insert_success");
                $send_email = !$this->user->ID && isset($user_info["email"]);
                try {
                    $this->user->map($user_info);
                    $this->user->save();
                    $this->user->updateRoles($roles);
                    if($send_email){
                        $reset_password = new ResetPassword();
                        $reset_password->user = $this->user->ID;
                        $reset_password->key = hash("SHA256", Utils::get_current_date().json_encode($this->user));
                        $reset_password->save();
                        $reset_link = BASE_URL."/reset_password/?USER=".$this->user->ID."&KEY=".$reset_password->key;
                        $mail = _t_email("user_insert", [SITE_NAME, $this->user->username, $reset_link, $reset_link]);
                        Utils::HTMLMail($this->user->email, _t("user_definition", [SITE_NAME]), $mail, $this->user->getFullName());
                    }
                    $this->create_warning_message($success_message, "alert-success");
                    Utils::core_go_to(BASE_URL . "/admin/user/{$this->user->username}");
                } catch (Exception $ex) {
                    $this->create_warning_message($ex->getMessage());
                }
            }
        } else if (isset($_POST["change_password"])) {
            if (!$this->checkCsrfToken(self::PASSWORD_FORM_ID)) {
                $this->create_warning_message(_t("invalid_operation"));
            } else {
                try {
                    $password_info = $_POST["password"];
                    if (($this->user->ID == User::get_current_core_user()->ID && $this->user->password != hash("SHA256", $password_info["current_pass"]))
                        || $password_info["password"] != $password_info["password2"]
                    ) {
                        throw new Exception(_t("password_be_sure_correct"));
                    } else {
                        $this->user->password = hash("SHA256", $password_info["password"]);
                        $this->user->save();
                        $this->create_warning_message(_t("update_success"), "alert-success");
                        Utils::core_go_to(BASE_URL . "/admin/user/{$this->user->username}");
                    }
                } catch (Exception $ex) {
                    $this->create_warning_message($ex->getMessage());
                }
            }
        }
        $current_user_roles = $this->user->getUserRoles();
        $excluded_user_roles = array_diff(User::getAllAvailableUserRoles(), $current_user_roles);
        foreach ($current_user_roles as $role) {
            $this->role_options[] = (new OptionField($role, $role))->addAttribute("selected", "true");
        }
        foreach ($excluded_user_roles as $role) {
            $this->role_options[] = (new OptionField($role, $role));
        }

        $this->form_build_id = $this->createCsrf(self::FORM_ID);
        $this->form_token = $this->createFormToken($this->form_build_id);
    }

    protected function echoContent()
    {
        $password_form_build_id = $this->createCsrf(self::PASSWORD_FORM_ID);
        $password_form_token = $this->createFormToken($password_form_build_id);

        $password_entry = new FormBuilder("POST");
        $password_entry->addClass("col-12");
        if ($this->user->ID == User::get_current_core_user()->ID) {
            $password_entry->addField(
                InputField::create("password[current_pass]")
                    ->setLabel(_t("current_pass"))
                    ->setType("password")
                    ->addAttribute("autocomplete", "off")
            );
        }
        $password_entry->addField(
            InputField::create("password[password]")
                ->setLabel(_t("password"))
                ->setType("password")
                ->addAttribute("autocomplete", "new-password")
        )->addField(
            InputField::create("password[password2]")
                ->setLabel(_t("password_again"))
                ->setType("password")
                ->addAttribute("autocomplete", "new-password")
        )->addField(
            InputField::create("change_password")
                ->setValue(_t("update_password"))
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
        echo $container;
    }
}
