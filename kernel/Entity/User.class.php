<?php
define("PASSWORD_FALSE_COUNT", "PASSWORD_FALSE_COUNT");
define("LOGIN_UNTRUSTED_ACTIONS", "LOGIN_UNTRUSTED_ACTIONS");
class User extends DBObject
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
    public static function get(array $filter, string $table = self::TABLE): ?User
    {
        return parent::get($filter, self::TABLE);
    }

    /**
     * @Override
     */
    public static function getAll(array $filter, string $table = self::TABLE): array
    {
        return parent::getAll($filter, $table);
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
                throw new Exception(_t("username_exist"));
            } else if (!$this->checkEmailInsertAvailable()) {
                throw new Exception(_t("email_not_available"));
            }
            $this->status = User::STATUS_ACTIVE;
        }
        return parent::insert();
    }

    protected function update()
    {
        if (get_called_class() === User::class) {
            if (!$this->checkEmailUpdateAvailable()) {
                throw new Exception(_t("email_not_available"));
            }
        }
        return parent::update();
    }

    public function delete(): bool
    {
        return db_delete("users_roles")->condition("user_id = :user_id", ["user_id" => $this->ID])->execute() &&
            db_delete("logins")->condition("username = :username", ["username" => $this->username])->execute() &&
            db_delete("reset_password_queue")->condition("USER = :user_id", ["user_id" => $this->ID])->execute()
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
        return !$user ? TRUE : self::getUserByEmail($this->email)->ID === $this->ID;
    }

    public static function validatePassword(string $password)
    {
        return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\p{P})[a-zA-Z\d\p{P}]{8,}$/", $password);
    }

    public function updateRoles(array $roles)
    {
        $excluded_roles = array_diff($this->getUserRoles(TRUE), $roles);
        foreach ($excluded_roles as $role) {
            $this->delete_role($role);
        }
        $added_roles = array_diff($roles, $this->getUserRoles(TRUE));
        foreach ($added_roles as $role) {
            $this->add_role($role);
        }
    }


    public static function login($username, $password)
    {
        //if ip address is blocked not let to login
        if (self::is_ip_address_blocked()) {
            throw new Exception(_t("ip_blocked"));
        }
        /**
         * @var User $user
         */
        $user = self::getUserByUsername($username);
        if ($user && $user->status == self::STATUS_BLOCKED) {
            throw new Exception(_t("account_blocked"));
        }

        //if login fails for more than 10 times block this ip
        if (isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS]) && $_SESSION[LOGIN_UNTRUSTED_ACTIONS] > 10) {
            if (self::get_login_try_count_of_ip() > 10) {
                self::block_ip_address();
            }
            if (self::get_login_try_count_of_user($username) > 10) {
                //blocking user
                $user->status = self::STATUS_BLOCKED;
                $user->save();
            }
            throw new Exception(_t("ip_blocked"));
        }
        if (!$user || !password_verify($password, $user->password) ) {
            if (isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS])) {
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS]++;
                if ($_SESSION[LOGIN_UNTRUSTED_ACTIONS] > 3) {
                    throw new Exception(_t("too_many_login_fails"));
                }
            } else {
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS] = 1;
            }
            throw new Exception(_t("wrong_credidental"));
        }
        //login successful
        global $current_user;
        $current_user = $user;
        $current_user->access = Utils::get_current_date();
        $current_user->save();
        $_SESSION[BASE_URL . "-UID"] = $user->ID;
        if (isset($_POST["remember-me"]) && $_POST["remember-me"]) {
            $jwt = new JWT();
            $payload = new stdClass();
            $payload->ID = $current_user->ID;
            $jwt->setPayload($payload);
            setcookie("session-token", $jwt->createToken(), strtotime("+1 day"), SITE_ROOT, $_SERVER["HTTP_HOST"], false, true);
            setcookie("remember-me", true, strtotime("+1 year"), SITE_ROOT, $_SERVER["HTTP_HOST"], false, true);
        } else {
            setcookie("remember-me", false, null);
        }

        Watchdog::log("login", $user->username);

        unset($_SESSION[PASSWORD_FALSE_COUNT]);
        unset($_SESSION[LOGIN_UNTRUSTED_ACTIONS]);
        return $user;
    }

    public function isRoot()
    {
        return $this->username == "root";
    }

    public function isAdmin()
    {
        return $this->isUserInRole("ADMIN");
    }

    public function isLoggedIn(): bool
    {
        return $this->username != "guest";
    }

    public function getUserRoles(bool $force = FALSE)
    {
        if (!$this->ROLES || $force) {
            $query = db_select("users_roles")
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
        $this->ROLES = NULL;
        return db_insert("users_roles", ["user_id" => $this->ID, "role_id" => Role::get(["role" => $role])->ID])->execute();
    }
    public function delete_role(string $role)
    {
        $this->ROLES = NULL;
        return db_delete("users_roles")
            ->condition("user_id = :user_id AND role_id = :role_id", [":user_id" => $this->ID, ":role_id" => Role::get(["role" => $role])->ID])
            ->execute();
    }


    public static function getAllAvailableUserRoles()
    {
        if (!self::$ALLROLES) {
            self::$ALLROLES = array_map(function($el){
                return $el->role;
            }, db_select("roles")->execute()->fetchAll(PDO::FETCH_OBJ));
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
        list($input, $wrapper_class) = CoreDB::get_supported_data_types()[$this->get_input_type($description["Type"], $description["Key"])]["input_field_callback"]($this, $description, $this->table);
        if (in_array($description["Field"], ["ID", "created_at", "password", "access", "status"])) {
            $wrapper_class = "d-none";
            $input = InputField::create("")->setType("hidden");
        } else if ($description["Field"] == "email") {
            $input->setType("email");
            $wrapper_class = "col-12";
        } else if ($description["Field"] == "phone") {
            $input->setType("tel");
            $wrapper_class = "col-12";
        } else {
            $wrapper_class = "col-12";
        }
        $input->setLabel(_t($description["Field"]));
        return [$input, $wrapper_class];
    }

    protected function getSubmitSection()
    {
        return InputField::create("save")
            ->setType("submit")
            ->addClass("btn btn-outline-success")
            ->setValue(_t("save"));
    }
}
