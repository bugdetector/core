<?php
!defined("DIRECT_OBJECT_REF_SHIELD") ? die(http_response_code(403)) : "";

class Utils{
    public static function include_dir($folder){
        foreach (glob("{$folder}/*.php") as $filename)
        {
            include $filename;
        }
    }
    public static function create_warning_message(string $message, string $type = "alert-danger"){
        if(!isset($_SESSION["messages"])){
            $_SESSION["messages"] = [];
        }
        if( !isset($_SESSION["messages"][$type]) ){
            $_SESSION["messages"][$type] = [];
        }
        $_SESSION["messages"][$type] = [
            "message" => $message
        ];
    }
    
    public static function get_message_header($type){
        $headers = [
            "alert-info" => 52,
            "alert-danger" => 53,
            "alert-warning" => 54,
            "alert-success" => 0
        ];
        return _t($headers[$type]);
    }
    
    public static function get_current_date() {
        return date("Y-m-d H:i:s");
    }
    
    public function get_user_ip()
    {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }
    
    public function HTMLMail($email,$subject,$message, $username) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        $mail->IsHTML(true);
        $mail->SetLanguage("en", "phpmailer/language");
        $mail->CharSet  ="utf-8";
        $mail->Username = EMAIL;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SetFrom(EMAIL, EMAIL_USERNAME);
        $mail->AddAddress($email, $username);
        $mail->Subject = $subject;
        $mail->Body = $message;
        return $mail->Send();
    }
    
    public static function normalizeFiles(array $files = NULL){
        if(!$files){
            $files = $_FILES;
        }
        $normalized_files = [];
        if(empty($files) || (isset($files["name"]) && !is_array($files["name"]) )){
            $normalized_files = $files;
        }else if(!isset($files["name"])){
            foreach ($files as $key => $file){
                $normalized_files[$key] = self::normalizeFiles($file);
            }
        }else{
            foreach ($files["name"] as $key => $name){
                $normalized_files[$key] = [
                    "name" => $name,
                    "type" => $files["type"][$key],
                    "tmp_name" => $files["tmp_name"][$key],
                    "error" => $files["error"][$key],
                    "size" => $files["size"][$key]
                ];
            }
        }
        return $normalized_files;
    }
}

/**
 * 
 * @global User $current_user
 * @return User
 */
function get_current_core_user() {
    global $current_user;
    if($current_user){
        return $current_user;
    } else {
        if(isset($_SESSION[BASE_URL."-UID"])){
            $current_user = User::getUserById($_SESSION[BASE_URL."-UID"]);
        }elseif(isset($_COOKIE["session-token"])){
            $jwt = JWT::createFromString($_COOKIE["session-token"]);
            $current_user = User::getUserById($jwt->getPayload()->ID);
            $_SESSION[BASE_URL."-UID"] = $current_user->ID;
        }else{
            $current_user = User::getUserByUsername("guest");
        }
    }
    return $current_user;
}

function store_uploaded_file($table, $field_name, $file) {
    $file_url = "files/uploaded/$table/$field_name/";
    is_dir($file_url) ?  : mkdir($file_url, 0777, true);
    $file_url.= $file["name"];
    return move_uploaded_file($file["tmp_name"], $file_url);        
}

function remove_uploaded_file($table, $field_name, $file) {
    $file_url = __DIR__."/files/uploaded/$table/$field_name/$file";
    if(is_file($file_url)) unlink($file_url);
}

function core_go_to(string $uri) {
    header("Location: $uri");
    die();
}


define("ENCRYPTION_METHOD", "aes128");
function create_csrf(string $form_id, $value) {
    $encryption_key = bin2hex(random_bytes(10));
    $form_build_id = @openssl_encrypt($form_id, ENCRYPTION_METHOD,  $encryption_key);
    $_SESSION[$form_build_id] = [
        "encryption_key" => $encryption_key,
        "value" => $value
    ];
    return $form_build_id;
}

function get_csrf(string $form_build_id, string $form_id) {
    if(isset($_SESSION[$form_build_id])){
        $encryption_key = $_SESSION[$form_build_id]["encryption_key"];
        $value = $_SESSION[$form_build_id]["value"];
        
        $decrypted_form_id = openssl_decrypt($form_build_id, ENCRYPTION_METHOD, $encryption_key);
        if($form_id != $decrypted_form_id){
            throw new Exception(_t(95));
        }
        unset($_SESSION[$form_build_id]);
        return $value;
    }
}