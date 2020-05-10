<?php

class AdminManageUserController extends AdminManageController
{

    protected function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(_t("user_management"));

        $this->table_headers = [
            "ID" => "ID",
            "username" => _t("username"),
            "name" => _t("name"),
            "surname" => _t("surname"),
            "email" => _t("email"),
            "phone" => _t("phone"),
            "created_at" => _t("created_at"),
            "access" => _t("last_access")
        ];
        $params = array_filter($_GET);
        $order_by = isset($params["orderby"]) && in_array($params["orderby"], array_keys($this->table_headers)) ? $params["orderby"] : "ID";
        $order_direction = isset($params["orderdirection"]) && $params["orderdirection"] == "DESC" ? "DESC" : "ASC";
        unset($params["orderby"], $params["orderdirection"]);


        $query = db_select(User::TABLE)
            ->orderBy($order_by . " " . $order_direction)
            ->condition("username != 'guest'");
        foreach ($params as $key => $value) {
            if (in_array($key, ["created_at", "access"])) {
                $dates = explode("&", $params[$key]);
                $query->condition(
                    "`{$key}` >= :{$key}_start AND `{$key}` <= :{$key}_end",
                    [
                        ":{$key}_start" => $dates[0]." 00:00:00",
                        ":{$key}_end" => $dates[1]." 23:59:59"
                    ]
                );
            }else if (in_array($key, array_keys($this->table_headers))) {
                $query->condition(" $key LIKE :$key ", [":$key" => "%" . $value . "%"]);
            }
        }

        $this->total_count = $query->select_with_function(["COUNT(*) AS count"])->execute()->fetchObject()->count;
        $query->unset_fields();
        $this->table_content = $query
            ->select(User::TABLE, ["ID", "username", "name", "surname", "email", "phone", "created_at", "access"])
            ->select_with_function([
                "CONCAT(\"<a href='" . BASE_URL . "/admin/user/\", username, \"'>" . _t("edit_user") . "</a>\") AS edit_link",
                "CONCAT(\"<a href='#' class='delete-user' data-username='\", username, \"'>" . _t("remove_user") . "</a>\") AS remove_link"
            ])
            ->limit(PAGE_SIZE_LIMIT, ($this->page - 1) * PAGE_SIZE_LIMIT)->execute()->fetchAll(PDO::FETCH_ASSOC);


        $this->action_section = Group::create("d-sm-inline-block btn btn-sm btn-primary shadow-sm")
            ->setTagName("a")->addAttribute("href", BASE_URL . "/admin/user?q=insert")->addField(
                TextElement::create("<i class='fas fa-user-plus text-white-50'></i> " . _t("add_user"))
            );

        $this->filter_options = new FormBuilder();
        $this->filter_options->addClass("row");
        foreach ($this->table_headers as $key => $text) {
            $input = InputField::create($key)->setLabel($text)
                ->setValue(isset($params[$key]) ? $params[$key] : "")
                ->addAttribute("autocomplete", "off");
            if (in_array($key, ["created_at", "access"])) {
                $input->addClass("daterangeinput");
            } else if ($key == "ID") {
                $input->setType("number");
            }
            $this->filter_options->addField(
                Group::create("col-md-3 col-sm-4")->addField(
                    $input
                )
            );
        }
        $this->add_js_files("pages/admin/manage/user/user.js");
    }


    protected function add_default_translations()
    {
        parent::add_default_translations();
        $this->add_frontend_translation("remove_user_accept");
    }
}
