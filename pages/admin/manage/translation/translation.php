<?php

class AdminManageTranslationController extends AdminManageController
{

    protected function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(_t("translations"));

        $this->table_headers = [];
        foreach (CoreDB::get_table_description(Translation::TABLE) as $field) {
            $this->table_headers[$field["Field"]] = $field["Field"];
        }
        unset($this->table_headers["created_at"], $this->table_headers["last_updated"]);

        $params = array_filter($_GET);
        $order_by = isset($params["orderby"]) && in_array($params["orderby"], array_keys($this->table_headers)) ? $params["orderby"] : "ID";
        $order_direction = isset($params["orderdirection"]) && $params["orderdirection"] == "DESC" ? "DESC" : "ASC";
        unset($params["orderby"], $params["orderdirection"]);

        $query = db_select(Translation::TABLE)->orderBy("`$order_by` $order_direction");
        foreach ($params as $key => $value) {
            if (in_array($key, array_keys($this->table_headers))) {
                $query->condition(" `$key` LIKE :$key ", [":$key" => "%" . $value . "%"]);
            }
        }

        $this->total_count = $query->select_with_function(["COUNT(*) AS count"])->execute()->fetchObject()->count;
        $query->unset_fields();

        $this->table_content = $query
        ->select(Translation::TABLE, $this->table_headers)
        ->limit(PAGE_SIZE_LIMIT, ($this->page - 1)* PAGE_SIZE_LIMIT)
        ->execute()->fetchAll(PDO::FETCH_NUM);

        $this->action_section = Group::create("")->addField(
            Group::create("d-sm-inline-block btn btn-sm btn-primary shadow-sm lang-imp")
                ->setTagName("a")->addAttribute("href", "#")->addField(
                    TextElement::create("<i class='fas fa-file-import text-white-50'></i> " . _t("import"))
                )
        )->addField(
            Group::create("d-sm-inline-block btn btn-sm btn-primary shadow-sm ml-1 lang-exp")
                ->setTagName("a")->addAttribute("href", "#")->addField(
                    TextElement::create("<i class='fas fa-file-export text-white-50'></i> " . _t("export"))
                )
        );

        $this->add_js_files("pages/admin/manage/translation/translation.js");

        $this->filter_options = new FormBuilder();
        $this->filter_options->addClass("row");
        $this->filter_options->addField(
            Group::create("col-md-3 col-sm-4")->addField(
                InputField::create("ID")
                ->setType("number")->setLabel("ID")
                ->setValue(isset($params["ID"]) ? $params["ID"] : "")
                ->addAttribute("autocomplete", "off")
            )
        )->addField(
            Group::create("col-md-3 col-sm-4")->addField(
                InputField::create("key")->setLabel("key")
                ->setValue(isset($params["key"]) ? $params["key"] : "")
                ->addAttribute("autocomplete", "off")
            )
        )->addField(
            Group::create("col-md-3 col-sm-4")->addField(
                InputField::create("en")->setLabel("en")
                ->setValue(isset($params["en"]) ? $params["en"] : "")
                ->addAttribute("autocomplete", "off")
            )
        )->addField(
            Group::create("col-md-3 col-sm-4")->addField(
                InputField::create("tr")->setLabel("tr")
                ->setValue(isset($params["tr"]) ? $params["tr"] : "")
                ->addAttribute("autocomplete", "off")
            )
        );
    }

    protected function add_default_translations()
    {
        parent::add_default_translations();
        $this->add_frontend_translation("lang_import_info");
        $this->add_frontend_translation("lang_export_info");
    }
}
