<?php

namespace Src\Entity;

use CoreDB;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\TableMapper;
use Exception;
use Src\Form\UserInsertForm;

class User extends TableMapper
{
    public File $profile_photo;
    public ShortText $username;
    public ShortText $name;
    public ShortText $surname;
    public ShortText $email;
    public ShortText $phone;
    protected ShortText $password;
    public Checkbox $active;
    public DateTime $last_access;

    public EntityReference $roles;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "users";
    }

    /**
     * @inheritdoc
     */
    public function map(array $array, bool $isConstructor = false)
    {
        if (isset($array["password"]) && !$array["password"]) {
            unset($array["password"]);
        }
        unset($array["last_access"]);
        parent::map($array);
    }

    public function getForm()
    {
        return new UserInsertForm($this);
    }

    public function getResultHeaders(bool $translateLabel = true) : array
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

    public function getResultQuery(): SelectQueryPreparerAbstract
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
            ->select_with_function(["GROUP_CONCAT(roles.role SEPARATOR '\n') AS roles"])
            ->select(
                "u",
                [
                    "last_access",
                    "created_at"
                ]
            )->leftjoin("users_roles", "ur", "ur.user_id = u.ID")
            ->leftjoin(Role::getTableName(), "roles", "ur.role_id = roles.ID")
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
        } elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            throw new Exception(Translation::getTranslation("enter_valid_mail"));
        }
         elseif (!$this->checkEmailInsertAvailable()) {
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
            $this->password->setValue(password_hash($this->password, PASSWORD_BCRYPT));
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

    public function isAdmin()
    {
        return $this->isUserInRole("Admin");
    }

    public function isLoggedIn(): bool
    {
        return $this->username->getValue() != "guest";
    }

    public function isUserInRole(string $role) : bool
    {
        $roleObj = Role::get(["role" => $role]);
        if($roleObj){
            return in_array($roleObj->ID->getValue(), $this->roles->getValue());
        }
        return false;
    }

    public static function getAllAvailableUserRoles()
    {
        return array_map(function ($el) {
            return $el->role;
        }, Role::getAll([]));
    }

    public static function getUserIp()
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

    public static function blockIpAddress()
    {
        $blocked_ip = new BlockedIp();
        $blocked_ip->ip->setValue(self::getUserIp());
        $blocked_ip->save();
    }
    public static function isIpAddressBlocked(): bool
    {
        return boolval(BlockedIp::get(["ip" => self::getUserIp()]));
    }

    public static function getLoginTryCountOfIp()
    {
        return count(Logins::getAll(["ip_address" => self::getUserIp()]));
    }

    public static function getLoginTryCountOfUser(string $username)
    {
        return count(Logins::getAll(["username" => $username, "ip_address" => self::getUserIp()]));
    }

    public function getFullName(): string
    {
        return "{$this->name} {$this->surname}";
    }
}
