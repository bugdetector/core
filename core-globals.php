<?php
!defined("DIRECT_OBJECT_REF_SHIELD") ? die(http_response_code(403)) : "";

function include_dir($folder){
    foreach (glob("{$folder}/*.php") as $filename)
    {
        include $filename;
    }
}

function create_warning_message(string $message, string $type = "alert-danger"){
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

function get_message_header($type){
    $headers = [
        "alert-info" => 52,
        "alert-danger" => 53,
        "alert-warning" => 54,
        "alert-success" => 0
    ];
    return _t($headers[$type]);
}

/**
 * 
 * @global User $current_user
 * @return User
 */
function get_current_core_user() {
    global $current_user;
    return $current_user;
}

function get_user_ip()
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

function include_files_for_object(DBObject &$object) {
    foreach ($_FILES as $file_key => $file){
        if($file["size"] != 0){
            $file["name"] = $object->ID."_".filter_var($file["name"], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
            $object->$file_key = $file["name"];
            if(!store_uploaded_file($object->table, $file_key, $file)){
                CoreDB::getInstance()->rollback();
                throw new Exception(_t(99));
            }
        }
    }
    $object->update();
}

function store_uploaded_file($table, $field_name, $file) {
    $file_url = "files/uploaded/$table/$field_name/";
    is_dir($file_url) ?  : mkdir($file_url, 0777, true);
    $file_url.= $file["name"];
    return move_uploaded_file($file["tmp_name"], $file_url);        
}

function remove_uploaded_file($table, $field_name, $file) {
    $file_url = "files/uploaded/$table/$field_name/$file";
    unlink($file_url);
}

function control_real_object_with_params($table, $id = 0) {
    $realObject = new DBObject($table);
    $realObject->getById($id != 0 ? $id : $_POST["ID"]);
    foreach ($_FILES as $file_key => $file) {
        if($file["size"] != 0){
            if($_POST[$file_key] !== $realObject->$file_key){
                CoreDB::getInstance()->rollback();
                throw new Exception(_t(67));
            }else{
                $file_url = "files/uploaded/$table/$file_key/".$realObject->$file_key;
                is_file($file_url) ? unlink($file_url) : NOEXPR;
            }
        }
    }
}

function get_current_date() {
    return date("Y-m-d H:i:s");
}

function core_go_to(string $uri) {
    header("Location: $uri");
    die();
}

function HTMLMail($email,$subject,$message, $username) {
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

function prepare_select_box_from_query_result(PDOStatement $result, string $name, $null_element = NULL, $selected_value = "" ,array $classes = []){
    $result_array = $result->fetchAll(PDO::FETCH_NUM);
    $select_array = [];
    foreach ($result_array as $row) {
        $select_array[$row[0]] = $row[1];
    }
    return prepare_select_box($select_array, $name, $null_element, $selected_value ,$classes);
}


function prepare_select_box(array $elements, string $name, $null_element = NULL, $selected_value ,array $classes = []){
    $out = "<select name='$name' class='selectpicker form-control".implode("",$classes)."'>".
    ($null_element ? "<option value='0'>$null_element</option>" : "");

    foreach($elements as $key => $value){
        $out.="<option value='$key' ".($key == $selected_value ? "selected" : "").">$value</option>";
    }
    $out.="</select>";
    return $out;
}