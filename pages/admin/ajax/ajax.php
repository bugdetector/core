<?php

class AdminAjaxController extends ServicePage
{

    public function callService(string $service_name)
    {
        $this->$service_name();
    }

    public function check_access(): bool
    {
        return User::get_current_core_user()->isAdmin();
    }

    /**
     * Delete record
     */
    private function delete()
    {
        if (in_array($_POST["table"], CoreDB::get_information_scheme())) {
            $table = $_POST["table"];
            $id = $_POST["id"];
            $object = DBObject::get(["ID" => $id], $table);
            if ($object) {
                try {
                    $object->delete();
                    $this->send_result(_t("record_removed"));
                } catch (Exception $ex) {
                    $this->throw_exception_as_json($ex->getMessage());
                }
            } else {
                $this->throw_exception_as_json(_t("invalid_operation"));
            }
        }
    }

    /**
     * Returns table list
     */
    private function get_table_list()
    {
        echo json_encode(CoreDB::get_information_scheme());
    }

    /**
     * Returns foreign key entry
     */
    private function get_fk_entry()
    {
        $description = CoreDB::get_foreign_key_description($_POST["table"], $_POST["column"]);
        $object = new DBObject($description[0]);
        $object->getById(intval($_POST["fk"]));
        $return_string = "";
        foreach ($object->toArray() as $key => $field) {
            $return_string .= "$key = $field ";
        }
        echo $return_string;
    }

    /**
     * Returns field definition for new table definition
     */
    private function get_input_field()
    {
        $field = new FieldDefinitionRow();
        $field->setIndex($_POST["index"]);
        echo $field;
    }

    /**
     * Drops table
     */
    private function drop()
    {
        $tablename = $_POST["tablename"];
        if (in_array($tablename, CoreDB::get_information_scheme())) {
            db_drop($tablename)->execute();
            db_truncate(Cache::TABLE);
            echo json_encode(["status" => "true", "message" => _t("table_deleted", [$tablename])]);
        }
    }

    /**
     * Drops table or field
     */
    private function dropfield()
    {
        $tablename = $_POST["tablename"];
        $column = $_POST["column"];
        if (in_array($tablename, CoreDB::get_information_scheme())) {
            db_drop($tablename)->setColumn($column)->execute();
            db_truncate(Cache::TABLE);
            echo json_encode(["status" => "true", "message" => _t("field_dropped", [$column])]);
        }
    }

    /**
     * Truncates table
     */
    private function truncate()
    {
        $tablename = $_POST["tablename"];
        if (in_array($tablename, CoreDB::get_information_scheme())) {
            db_truncate($tablename);
            echo json_encode(["status" => "true", "message" => _t("table_truncated", [$tablename])]);
        }
    }

    /**
     * Removes user
     */
    private function delete_user()
    {
        $username = $_POST["username"];
        if ($user_to_delete = User::getUserByUsername($username)) {
            CoreDB::getInstance()->beginTransaction();
            $user_to_delete->delete();
            CoreDB::getInstance()->commit();
            $this->send_result(_t("deleted", [$user_to_delete->username]));
        }else{
            $this->throw_exception_as_json(_t("invalid_operation"));
        }
    }

    /**
     * Create new role
     */
    private function add_role()
    {
        try {
            $role = new Role();
            $role->role = $_POST["role"];
            $role->save();
            $this->send_result(_t("role_defined", [$role->role]), "message");
        } catch (Exception $ex) {
            $this->throw_exception_as_json($ex->getMessage());
        }
    }
    /**
     * Removes role
     */
    private function remove_role()
    {
        $role = Role::get(["role" => $_POST["ROLE"]]);
        if (!$role) {
            $this->throw_exception_as_json(_t("invalid_operation"));
        }
        $user = db_select("users_roles")->condition("role_id = :role_id", ["role_id" => $role->ID])->limit(1)->execute()->fetchAll(PDO::FETCH_NUM);
        if (count($user) > 0) {
            $this->throw_exception_as_json(_t("role_remove_error_user_exist"));
        }
        $role->delete();

        $this->send_result(_t("role_removed"));
    }

    private function langimp()
    {
        try {
            Translation::importTranslations();
            $this->send_result(_t("import_success"));
        } catch (Exception $ex) {
            $this->throw_exception_as_json($ex->getMessage());
        }
    }

    private function langexp()
    {
        try {
            Translation::exportTranslations();
            $this->send_result(_t("export_success"));
        } catch (Exception $ex) {
            $this->throw_exception_as_json($ex->getMessage());
        }
    }
}
