<?php
!defined("DIRECT_OBJECT_REF_SHIELD") ? die(http_response_code(403)) : "";

class Utils{
    const ENCRYPTION_METHOD = "aes128";

    public static function include_dir($folder){
        foreach (glob("{$folder}/*.php") as $filename)
        {
            include $filename;
        }
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
    
    public static function HTMLMail($email,$subject,$message, $username) {
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

    public static function core_go_to(string $uri) {
        header("Location: $uri");
        die();
    }
    
    public static function store_uploaded_file($table, $field_name, $file) {
        $file_url = "files/uploaded/$table/$field_name/";
        is_dir($file_url) ?  : mkdir($file_url, 0777, true);
        $file_url.= $file["name"];
        return move_uploaded_file($file["tmp_name"], $file_url);        
    }
    
    public static function remove_uploaded_file($table, $field_name, $file) {
        $file_url = __DIR__."/../files/uploaded/$table/$field_name/$file";
        if(is_file($file_url)) unlink($file_url);
    }
}