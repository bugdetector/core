<?php

namespace Src\Entity;

use CoreDB;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\TableMapper;
use Exception;
use PDO;
use Src\Form\UserInsertForm;

define("PASSWORD_FALSE_COUNT", "PASSWORD_FALSE_COUNT");
define("LOGIN_UNTRUSTED_ACTIONS", "LOGIN_UNTRUSTED_ACTIONS");
class User extends TableMapper
{
    public ShortText $username;
    public ShortText $name;
    public ShortText $surname;
    public ShortText $email;
    public ShortText $phone;
    protected ShortText $password;
    public Checkbox $active;
    public DateTime $last_access;

    public EntityReference $roles;
    private $ROLES;
    private static $ALLROLES;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "users";
    }

    public function map(array $array)
    {
        if (!$array["password"]) {
            unset($array["password"]);
        }
        unset($array["last_access"]);
        parent::map($array);
    }

    public function getForm()
    {
        return new UserInsertForm($this);
    }

    public function getTableHeaders(bool $translateLabel = true) : array
    {
        $headers = [""];
        $fields = [
            "ID",
            "username",
            "name",
            "surname",
            "email",
            "phone",
            "roles",
            "last_access",
            "created_at"
        ];
        foreach ($fields as $header) {
            $headers[$header] = $translateLabel ? Translation::getTranslation($header) : $header;
        }
        return $headers;
    }

    public function getTableQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()->select($this->getTableName(), "u")
            ->select("u", [
                "ID AS edit_actions",
                "ID",
                "username",
                "name",
                "surname",
                "email",
                "phone"
            ])
            ->select_with_function(["GROUP_CONCAT(r.role SEPARATOR '\n') AS roles"])
            ->select(
                "u",
                [
                    "last_access",
                    "created_at"
                ]
            )->leftjoin("users_roles", "ur", "ur.user_id = u.ID")
            ->leftjoin(Role::getTableName(), "r", "ur.role_id = r.ID")
            ->groupBy("u.ID");
    }

    public static function getUserByUsername(string $username)
    {
        return User::get(["username" => $username]);
    }

    public static function getUserByEmail(string $email)
    {
        return User::get(["email" => $email]);
    }

    protected function insert()
    {
        if (!$this->checkUsernameInsertAvailable()) {
            throw new Exception(Translation::getTranslation("username_exist"));
        } elseif (!$this->checkEmailInsertAvailable()) {
            throw new Exception(Translation::getTranslation("email_not_available"));
        }
        return parent::insert();
    }

    protected function update()
    {
        if (get_called_class() === User::class) {
            if (!$this->checkEmailUpdateAvailable()) {
                throw new Exception(Translation::getTranslation("email_not_available"));
            }
        }
        return parent::update();
    }

    public function save()
    {
        if (isset($this->changed_fields["password"]) && $this->changed_fields["password"]["new_value"]) {
            //$this->password = password_hash($this->password, PASSWORD_BCRYPT);
        }
        return parent::save();
    }

    public function delete(): bool
    {
        return CoreDB::database()->delete("users_roles")->condition("user_id", $this->ID)->execute() &&
            CoreDB::database()->delete(Logins::getTableName())->condition("username", $this->username)->execute() &&
            CoreDB::database()->delete(ResetPassword::getTableName())->condition("user", $this->ID)->execute()
            && parent::delete();
    }

    public function checkUsernameInsertAvailable(): bool
    {
        return !(bool) self::getUserByUsername($this->username);
    }

    public function checkEmailInsertAvailable(): bool
    {
        return !(bool) self::getUserByEmail($this->email);
    }

    public function checkEmailUpdateAvailable(): bool
    {
        $user = self::getUserByEmail($this->email->getValue());
        return !$user ? true : self::getUserByEmail($this->email->getValue())->ID->getValue() === $this->ID->getValue();
    }

    public static function validatePassword(string $password)
    {
        return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\p{P})[a-zA-Z\d\p{P}]{8,}$/", $password);
    }

    public function updateRoles(array $roles)
    {
        $excluded_roles = array_diff($this->getUserRoles(true), $roles);
        foreach ($excluded_roles as $role) {
            $this->delete_role($role);
        }
        $added_roles = array_diff($roles, $this->getUserRoles(true));
        foreach ($added_roles as $role) {
            $this->add_role($role);
        }
    }

    public function isAdmin()
    {
        return $this->isUserInRole("ADMIN");
    }

    public function isLoggedIn(): bool
    {
        return $this->username != "guest";
    }

    public function getUserRoles(bool $force = false)
    {
        if (!$this->ROLES || $force) {
            $query = CoreDB::database()->select("users_roles", "", true)
                ->join("roles", "", "role_id = roles.ID")
                ->select("roles", ["ROLE"])
                ->condition("user_id", $this->ID)
                ->execute();
            $this->ROLES = array_map(function ($el) {
                return $el->ROLE;
            }, $query->fetchAll(PDO::FETCH_OBJ));
        }
        return $this->ROLES;
    }

    public function isUserInRole(string $role)
    {
        return in_array($role, $this->getUserRoles());
    }


    public function add_role(string $role)
    {
        $this->ROLES = null;
        $role = new DBObject("users_roles");
        $role->map([
            "user_id" => $this->ID,
            "role_id" => Role::get(["role" => $role])->ID
        ]);
        return $role->save();
    }
    public function delete_role(string $role)
    {
        $this->ROLES = null;
        return DBObject::get(
            [
                "user_id" => $this->ID,
                "role_id" => Role::get(["role" => $role])->ID
            ],
            "users_roles"
        )->delete();
    }


    public static function getAllAvailableUserRoles()
    {
        if (!self::$ALLROLES) {
            self::$ALLROLES = array_map(function ($el) {
                return $el->role;
            }, Role::getAll([]));
        }
        return self::$ALLROLES;
    }

    public static function get_user_ip()
    {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    public static function block_ip_address()
    {
        $blocked_ip = new BlockedIp();
        $blocked_ip->ip = self::get_user_ip();
        $blocked_ip->save();
    }
    public static function is_ip_address_blocked(): bool
    {
        return boolval(BlockedIp::get(["ip" => self::get_user_ip()]));
    }

    public static function get_login_try_count_of_ip()
    {
        return count(Logins::getAll(["ip_address" => self::get_user_ip()]));
    }

    public static function get_login_try_count_of_user(string $username)
    {
        return count(Logins::getAll(["username" => $username, "ip_address" => self::get_user_ip()]));
    }

    public function getFullName(): string
    {
        return "{$this->name} {$this->surname}";
    }
}
