<?php
// phpcs:ignoreFile
use CoreDB\Kernel\BaseController;
use CoreDB\Kernel\ConfigurationManager;
use CoreDB\Kernel\Database\MySQL\MySQLDriver;
use CoreDB\Kernel\Database\DatabaseDriver;
use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\Router;
use Src\Entity\Session;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;
use Src\Entity\Watchdog;
use Src\JWT;
use Src\Theme\CoreRenderer;
use Src\Views\EmailTemplate;

class CoreDB
{
    private static $currentUser;

    public static function currentDate()
    {
        return date("Y-m-d H:i:s");
    }

    /**
     * @var array $attachments
     *  Contains attachment data.
     *  [
     *      type = "content",
     *      content = $content,
     *      filename = $filename
     *  ]
     *    OR
     *  [
     *      type = "path",
     *      path = $path,
     *      filename = $filename
     *  ]
     */
    public static function HTMLMail($tos, $subject, $message, $toUsernames, array $attachments = [], $template = EmailTemplate::class)
    {
        if(ENVIROMENT != "production"){
            $message .= Translation::getTranslation("originally_send_to", [
                $tos
            ]);
            $tos = Variable::getByKey("test_email_send_address")
            ->value->getValue();
        }
        $siteMail = Variable::getByKey("email_address")->value->getValue();
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = Variable::getByKey("email_smtp_secure")->value->getValue();
        $mail->Host = Variable::getByKey("email_smtp_host")->value->getValue();
        $mail->Port = Variable::getByKey("email_smtp_port")->value->getValue();
        $mail->IsHTML(true);
        $mail->SetLanguage(Translation::getLanguage(), "phpmailer/language");
        $mail->CharSet  = "utf-8";
        $mail->Username = $siteMail;
        $mail->Password = Variable::getByKey("email_password")->value->getValue();
        $mail->SetFrom($siteMail, Variable::getByKey("email_username")->value->getValue());
        $toUsernames = explode(";", $toUsernames);
        foreach(explode(";", $tos) as $index => $to){
            $mail->AddAddress($to, @$toUsernames[$index] ?: current($toUsernames));
        }
        $mail->Subject = $subject;
        $mail->Body = CoreRenderer::getInstance()->renderView(new $template($message));
        foreach($attachments as $attachment){
            switch($attachment["type"]){
                case "content":
                    $mail->addStringAttachment(
                        $attachment["content"], 
                        $attachment["filename"]
                    );
                    break;
                case "file":
                    $mail->addAttachment(
                        $attachment["path"],
                        $attachment["filename"]
                    );
                break;
            }
        }
        return $mail->Send();
    }

    public static function goTo(string $uri, $params = [])
    {
        if (!empty($params)) {
            $uri .= "?" . http_build_query($params);
        }
        header("Location: $uri");
        die();
    }

    public static function requestUrl()
    {
        $count = 1;
        return str_replace(SITE_ROOT, "", parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), $count);
    }

    public static function cleanDirectory(string $path, bool $includeDirs = false)
    {
        if (!is_dir($path)) {
            return;
        }
        foreach (new DirectoryIterator($path) as $fileInfo) {
            if (!$fileInfo->isDot()) {
                if ($fileInfo->isDir()) {
                    self::cleanDirectory($fileInfo->getPathname(), $includeDirs);
                } else {
                    unlink($fileInfo->getPathname());
                }
            }
        }
        if ($includeDirs) {
            rmdir($path);
        }
    }

    public static function cleanXSS($data)
    {
        if ($data == strip_tags($data)) {
            // is HTML
            //If not html then no need to sanitize
            return $data;
        }
        $data = htmlspecialchars_decode($data);
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        $elements = $dom->getElementsByTagName('*');
        foreach ($elements as $element) {
            $allowed_attributes = ["class", "style"];
            if ($element->tagName == "a") {
                $element->setAttribute('href', htmlspecialchars($element->getAttribute('href')));
                $allowed_attributes[] = "href";
                $allowed_attributes[] = "target";
            } elseif ($element->tagName == "img") {
                $element->setAttribute('src', htmlspecialchars($element->getAttribute('src')));
                $allowed_attributes[] = "src";
            }
            foreach ($element->attributes as $attribute) {
                if (in_array($attribute->name, $allowed_attributes)) {
                    continue;
                } else {
                    $element->removeAttribute($attribute->name);
                }
            }
        }
        // we are done...
        return strip_tags(
            $dom->saveHTML(),
            "<table><thead><tbody><tr><td><label><strong><p><em><i><u><ul><li><a>" .
            "<img><blockquote><span><pre><code><br><h1><h2><h3><h4><h5><h6><div>"
        );
    }

    public static function baseHost()
    {
        if (defined("TRUSTED_HOSTS")) {
            $trusted_hosts = explode(",", TRUSTED_HOSTS);
            if (!in_array($_SERVER["HTTP_HOST"], $trusted_hosts)) {
                return $trusted_hosts[0];
            } else {
                return $_SERVER["HTTP_HOST"];
            }
        } else {
            return $_SERVER["HTTP_HOST"];
        }
    }

    /**
     * Returns current user.
     * @return User
     */
    public static function currentUser(): User
    {
        if (self::$currentUser) {
            return self::$currentUser;
        } else {
            $userClass = ConfigurationManager::getInstance()->getEntityInfo("users")["class"];
            $userClass::deleteExpiredSessions();
            $headers = getallheaders();
            if (isset($_SESSION[BASE_URL . "-UID"])) {
                $session = Session::get(["session_key" => session_id()]);
                if($session){
                    self::$currentUser = $userClass::get([
                        "ID" => $_SESSION[BASE_URL . "-UID"],
                        "status" => User::STATUS_ACTIVE
                    ]);
                    $session->map([
                        "last_access" => \CoreDB::currentDate()
                    ]);
                    $session->save();
                }
            } elseif (isset($_COOKIE["session-token"]) || isset($headers["Authorization"])) {
                $jwt = JWT::createFromString(@$_COOKIE["session-token"] ?: $headers["Authorization"]);
                /** @var Session */
                $session = Session::get([
                    "user" => $jwt->getPayload()->ID,
                    "remember_me_token" => @$_COOKIE["session-token"] ?: $headers["Authorization"]
                ]);
                if($session){
                    self::$currentUser = $userClass::get([
                        "ID" => $session->user->getValue(),
                        "status" => User::STATUS_ACTIVE
                    ]);
                    $session->session_key->setValue(session_id());
                    $session->save();
                    $_SESSION[BASE_URL . "-UID"] = self::$currentUser->ID;
                }
            }
            if (!self::$currentUser) {
                self::$currentUser = new $userClass();
                if (isset(self::$currentUser->username)) {
                    self::$currentUser->username->setValue("guest");
                }
            }
        }
        return self::$currentUser;
    }

    public static function userLogin(User $user, bool $rememberMe = false){
        $user->last_access->setValue(\CoreDB::currentDate());
        $user->save();
        $_SESSION[BASE_URL . "-UID"] = $user->ID;
        $session = Session::get(["session_key" => session_id()]) ?: new Session();
        $session->map([
            "session_key" => session_id(),
            "ip_address" => User::getUserIp(),
            "user" => $user->ID->getValue()
        ]);
        if ($rememberMe) {
            $jwt = new JWT();
            $payload = new stdClass();
            $payload->ID = $user->ID->getValue();
            $jwt->setPayload($payload);
            $token = $jwt->createToken();
            $session->map([
                "remember_me_token" => $token
            ]);
            $rememberMeTimeout = defined("REMEMBER_ME_TIMEOUT") ?
            "+" . REMEMBER_ME_TIMEOUT : "+" . User::DEFAULT_REMEMBER_ME_TIMEOUT;
            setcookie(
                "session-token",
                $token,
                strtotime($rememberMeTimeout),
                SITE_ROOT ?: "/",
                \CoreDB::baseHost(),
                $_SERVER['SERVER_PORT'] == 443
            );
        }
        $session->save();        
        Watchdog::log("login", $user->username);
    }

    public static function userLogout(){
        if (isset($_SESSION[BASE_URL . "-BACKUP-UID"])) {
            $user = User::get($_SESSION[BASE_URL . "-BACKUP-UID"]);
            unset($_SESSION[BASE_URL . "-BACKUP-UID"]);
            \CoreDB::userLogin($user);
        }else{
            $session = Session::get(["session_key" => session_id()]);
            if ($session) {
                $session->delete();
            }
            session_destroy();
            setcookie(
                "session-token",
                "",
                0,
                SITE_ROOT ?: "/",
                \CoreDB::baseHost(),
                $_SERVER['SERVER_PORT'] == 443
            );
        }
    }

    public static function database(): DatabaseDriver
    {
        return MySQLDriver::getInstance();
    }

    /**
     * Returns Messenger Instance
     * @return Messenger
     *  Messenger Instance
     */
    public static function messenger(): Messenger
    {
        return Messenger::getInstance();
    }

    /**
     * Returns active controller
     * @return BaseController
     * Active Controller
     */
    public static function controller(): BaseController
    {
        return Router::getInstance()->getController();
    }

    /**
     * Returns configuration manager
     * @return ConfigurationManager
     * Configuration Manager
     */
    public static function config(): ConfigurationManager
    {
        return ConfigurationManager::getInstance();
    }

    public static function isImage($path)
    {
        $imageInfo = getimagesize($path);
        $imageType = $imageInfo ? $imageInfo[2] : null;
        if (in_array($imageType, [
            IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG
        ])) {
            return true;
        }
        return false;
    }
}
