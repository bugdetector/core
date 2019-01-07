<?php
define("PASSWORD_FALSE_COUNT","PASSWORD_FALSE_COUNT");
define("LOGIN_UNTRUSTED_ACTIONS", "LOGIN_UNTRUSTED_ACTIONS");
class User extends DBObject{
    const TABLE = USERS;
    const STATUS_ACTIVE = "active";
    const STATUS_BLOCKED = "blocked";


    public $ID, $USERNAME,$NAME, $SURNAME, $EMAIL, $PHONE, $PASSWORD, $CREATED_AT, $ACCESS, $STATUS;
    public $ROLES;
    private static $ALLROLES;
    public function __construct() {
        $this->table = self::TABLE;
    }


    public static function getUserById(int $id){
        $user = new self();
        $result = db_select(self::TABLE)->condition("ID = :id")->params(["id" => $id])->execute()->fetch(PDO::FETCH_ASSOC);
        if(!$result){
            return FALSE;
        }
        object_map($user, $result);
        return $user;
    }
    public static function getUserByUsername(string $username){
        $user = new self();
        $result = db_select(self::TABLE)->condition("USERNAME = :username")->params([":username" => $username])->execute()->fetch(PDO::FETCH_ASSOC);
        if(!$result){
            return FALSE;
        }
        object_map($user, $result);
        return $user;
    }
    
    public static function getUserByEmail(string $email){
        $user = new self();
        $result = db_select(self::TABLE)->condition("EMAIL = :email")->params([":email" => $email])->execute()->fetch(PDO::FETCH_ASSOC);
        if(!$result){
            return FALSE;
        }
        object_map($user, $result);
        return $user;
    }
    
    public function insert(){
        $this->CREATED_AT = date("Y-m-d h:i:s");
        $this_as_array = convert_object_to_array($this);
        unset($this_as_array["ROLES"]);
        if(db_insert(self::TABLE, $this_as_array)->execute()){
            $this->ID = CoreDB::getInstance()->lastInsertId();
            return TRUE;
        }
    }
    
    public function delete():bool {
        return db_delete(USERS_ROLES)->condition("USER_ID = :user_id", ["user_id" => $this->ID])->execute() &&
        db_delete(LOGINS)->condition("USER_ID = :user_id", ["user_id" => $this->ID])->execute() &&
        db_delete(RESET_PASSWORD_QUEUE)->condition("USER = :user_id", ["user_id" => $this->ID])->execute()
                && parent::delete();
    }

    public function checkUsernameInsertAvailable(): bool {
        return !(bool)self::getUserByUsername($this->USERNAME);
    }
    
    public function checkEmailInsertAvailable(): bool {
        return !(bool)self::getUserByEmail($this->EMAIL);
    }
    
    public function checkEmailUpdateAvailable():bool {
        $user = self::getUserByEmail($this->EMAIL);
        return !$user ? TRUE : self::getUserByEmail($this->EMAIL)->ID === $this->ID;
    }

    public function update(){
        $this_as_array = convert_object_to_array($this);
        unset($this_as_array["ROLES"]);
        return db_update(self::TABLE, $this_as_array)->condition("ID = :id", ["id" => $this->ID])->execute();
    }
    
    public function updateRoles(array $roles) {
        $excluded_roles = array_diff($this->getUserRoles(TRUE), $roles);        
        foreach ($excluded_roles as $role){
            $this->delete_role($role);
        }
        $added_roles = array_diff($roles, $this->getUserRoles(TRUE) );
        foreach ($added_roles as $role) {
            $this->add_role($role);
        }
    }

    
    public static function login($username, $password){
        //if ip address is blocked not let to login
        if(is_ip_address_blocked()){
            throw new Exception(_t(96));
        }
        //if login fails for more than 10 times block this ip
        $user = self::getUserByUsername($username);
        if($user && $user->STATUS == self::STATUS_BLOCKED){
            throw new Exception(_t(97));
        }
        $login = new DBObject(LOGINS);
        $login->LOGIN_DATE = get_current_date();
        $login->IP_ADRESS = get_user_ip();
        $login->USER_ID = isset($user->ID) ? $user->ID : 2; // 2 = guest user id
        if(isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS]) && $_SESSION[LOGIN_UNTRUSTED_ACTIONS] > 10){
            if(get_login_try_count_of_ip() > 10){
                block_ip_address();
            }
            if(get_login_try_count_of_user($username) > 10){
                //blocking user
                $user->STATUS = self::STATUS_BLOCKED;
                $user->update();
            }
            throw new Exception(_t(96));            
        }
        $login->insert();
        if(!$user || $user->PASSWORD != hash("SHA256",$password) ){
            if(isset($_SESSION[LOGIN_UNTRUSTED_ACTIONS])){
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS]++;
                if($_SESSION[LOGIN_UNTRUSTED_ACTIONS] > 3 ){
                    throw new Exception(_t(25));
                }  
            }else{
                $_SESSION[LOGIN_UNTRUSTED_ACTIONS] = 1;
            }
            throw new Exception(_t(24));
        }
        //login successful
        global $current_user;
        $current_user = $user;
        $current_user->ACCESS = get_current_date();
        $current_user->update();
        $_SESSION[BASE_URL."-UID"] = $user->ID;
        
        Watchdog::log("login", $user->USERNAME);
        
        db_delete(LOGINS)
        ->condition(" IP_ADRESS = :ip OR USER_ID = :uid", [":ip" => get_user_ip(), ":uid" => $user->ID])
        ->execute();
        unset($_SESSION[PASSWORD_FALSE_COUNT]);
        unset($_SESSION[LOGIN_UNTRUSTED_ACTIONS]);
        return $user;
    }
    
    public function isAdmin(){
        return $this->isUserInRole("ADMIN");
    }
    
    public function getUserRoles(bool $force = FALSE ){
        if(!$this->ROLES || $force){
            $query = db_select(USERS_ROLES)
                    ->join(ROLES)
                    ->select(ROLES, ["ROLE"])
                    ->condition("USER_ID = :user_id AND ROLE_ID = ROLES.ID" , [":user_id" => $this->ID])
                    ->execute();
            $this->ROLES = [];
            while ($role = $query->fetch(PDO::FETCH_NUM)[0]){
                $this->ROLES[] = $role;
            }
        }        
        return $this->ROLES;
    }
    
    public function isUserInRole(string $role){
        return in_array($role, $this->getUserRoles());
    }
    
    public function add_role(string $role) {
        $this->ROLES = NULL;
        return db_insert(USERS_ROLES, ["USER_ID" => $this->ID, "ROLE_ID" => self::getIdOfRole($role) ])->execute();
    }
    public function delete_role(string $role) {
        $this->ROLES = NULL;
        return db_delete(USERS_ROLES)
                ->condition("USER_ID = :user_id AND ROLE_ID = :role_id", [":user_id" => $this->ID, ":role_id" => self::getIdOfRole($role)])
                ->execute();
    }


    public static function getAllAvailableUserRoles(){
        if(!self::$ALLROLES){
            $query = db_select(ROLES)->select(ROLES, ["ROLE"])->execute();
            self::$ALLROLES = [];
            while ($role = $query->fetch(PDO::FETCH_NUM)[0]){
                self::$ALLROLES[] = $role;
            }
        }
        return self::$ALLROLES;
    }
    
    public static function getIdOfRole(string $role){
        return db_select(ROLES)->select(ROLES, ["ID"])->condition("ROLE = :role", [":role" => $role])->execute()->fetch(PDO::FETCH_NUM)[0];
    }
}


function block_ip_address() {
    $blocked_ip = new DBObject(BLOCKED_IPS);
    $blocked_ip->IP = get_user_ip();
    $blocked_ip->insert();    
}
function is_ip_address_blocked() {
    return db_select(BLOCKED_IPS)->condition("IP = :ip", [":ip" => get_user_ip()])->limit(1)->execute()->rowCount();    
}

function get_login_try_count_of_ip() {
    return db_select(LOGINS)
           ->select("", ["count(*)"])
           ->condition("IP_ADRESS = :ip", [":ip" => get_user_ip()])
           ->execute()->fetch(PDO::FETCH_NUM)[0];    
}

function get_login_try_count_of_user(string $username) {
    return db_select(LOGINS,"l")
            ->join(USERS,"u")
           ->select("", ["count(*)"])
           ->condition("l.USER_ID = u.ID AND u.USERNAME = :uname", [":uname" => $username])
           ->execute()->fetch(PDO::FETCH_NUM)[0];    
}