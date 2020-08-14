<?php

namespace Src\Entity;

use CoreDB\Kernel\Database\DeleteQueryPreparer;
use CoreDB\Kernel\Database\SelectQueryPreparer;
use CoreDB\Kernel\TableMapper;
use Exception;
use Src\JWT;
use PDO;
use Src\Form\Widget\SelectWidget;

define("PASSWORD_FALSE_COUNT", "PASSWORD_FALSE_COUNT");
define("LOGIN_UNTRUSTED_ACTIONS", "LOGIN_UNTRUSTED_ACTIONS");
class User extends TableMapper
{
    const STATUS_ACTIVE = "active";
    const STATUS_BLOCKED = "blocked";
    const STATUS_PENDING = "pending";


    const TABLE = "users";
    public $ID;
    public $username;
    public $name;
    public $surname;
    public $email;
    public $phone;
    public $password;
    public $created_at;
    public $access;
    public $status;

    private $ROLES;
    private static $ALLROLES;
    public function __construct()
    {
        $this->table = self::TABLE;
    }

    /**
     * @Override
     */
    public static function get(array $filter): ?User
    {
        return parent::find($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter): array
    {
        return parent::findAll($filter, self::TABLE);
    }

    public static function clear(){
        parent::clearTable(self::TABLE);
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
        if (get_called_class() === User::class) {
            if (!$this->checkUsernameInsertAvailable()) {
                throw new Exception(Translation::getTranslation("username_exist"));
            } elseif (!$this->checkEmailInsertAvailable()) {
                throw new Exception(Translation::getTranslation("email_not_available"));
            }
            $this->status = User::STATUS_ACTIVE;
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

    public function delete(): bool
    {
        return (new DeleteQueryPreparer("users_roles"))->condition("user_id = :user_id", ["user_id" => $this->ID])->execute() &&
            (new DeleteQueryPreparer(Logins::TABLE))->condition("username = :username", ["username" => $this->username])->execute() &&
            (new DeleteQueryPreparer(ResetPassword::TABLE))->condition("USER = :user_id", ["user_id" => $this->ID])->execute()
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
        $user = self::getUserByEmail($this->email);
        return !$user ? true : self::getUserByEmail($this->email)->ID === $this->ID;
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
            $query = (new SelectQueryPreparer("users_roles", "", true))
                ->join("roles")
                ->select("roles", ["ROLE"])
                ->condition("user_id = :user_id AND role_id = roles.ID", [":user_id" => $this->ID])
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
            ["user_id" => $this->ID, 
            "role_id" => Role::get(["role" => $role])->ID
            ] ,"users_roles")->delete();
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

    /**
     *
     * @global User $current_user
     * @return User
     */
    public static function get_current_core_user()
    {
        global $current_user;
        if ($current_user) {
            return $current_user;
        } else {
            if (isset($_SESSION[BASE_URL . "-UID"])) {
                $current_user = User::get(["ID" => $_SESSION[BASE_URL . "-UID"]]);
            } elseif (isset($_COOKIE["session-token"])) {
                $jwt = JWT::createFromString($_COOKIE["session-token"]);
                $current_user = User::get(["ID" => ($jwt->getPayload())->ID]);
                $_SESSION[BASE_URL . "-UID"] = $current_user->ID;
            } else {
                $current_user = User::getUserByUsername("guest");
            }
        }
        return $current_user;
    }

    public function getFullName(): string
    {
        return "{$this->name} {$this->surname}";
    }


    protected function getFieldInput($description)
    {
        if(strpos($description["Type"], "enum") !== FALSE){
            $description["Type"] = "ENUM";
        }
        $input = \CoreDB::database()::get_supported_data_types()[$this->get_input_type($description["Type"], $description["Key"])]["input_field_callback"]($this, $description, $this->table);
        if ($description["Field"] == "ID") {
            $input->addAttribute("disabled", "true");
        } elseif ($description["Field"] == "password") {
            $input->setType("password")
            ->addAttribute("autocomplete", "new-password");
        } elseif ($description["Field"] == "email") {
            $input->setType("email");
        } elseif ($description["Field"] == "phone") {
            $input->setType("tel");
        } else if($description["Field"] == "status") {
            $input = SelectWidget::create("{$this->table}[status]")
            ->setOptions(User::getStatuses())
            ->setValue(strval($this->status));
        }
        $input->setLabel(Translation::getTranslation($description["Field"]));
        return $input;
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => Translation::getTranslation(self::STATUS_ACTIVE),
            self::STATUS_BLOCKED => Translation::getTranslation(self::STATUS_BLOCKED),
            self::STATUS_PENDING => Translation::getTranslation(self::STATUS_PENDING)
        ];
    }
}
