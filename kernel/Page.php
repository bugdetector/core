<?php

abstract class Page extends View {
    const ENCRYPTION_METHOD = "aes128";

    protected $arguments = [];
    protected $js_files = [];
    protected $js_codes = [];
    protected $css_files = [];
    protected $css_codes = [];
    protected $frontend_translations = [];
    protected $accessable_roles = ["USER"];
    protected $title = SITE_NAME;
    protected $body_classes = [];

    protected $form_build_id;
    protected $form_token;


    public function __construct(array $arguments){
        $this->arguments = $arguments;
    }

    public function check_access(): bool {
        $user_roles = User::get_current_core_user()->getUserRoles();
        return array_diff($this->accessable_roles, $user_roles) == 0 ? TRUE : FALSE;
    }

    public function setTitle(string $title){
        $this->title = $title;
    }


    public function echoPage(){
        $this->add_default_js_files();
        $this->add_default_css_files();
        $this->add_default_translations();
        
        $this->preprocessPage();
        echo "<!DOCTYPE html>";
        echo "<html>";
        $this->echoHeader();
        echo '<body class="'.implode(" ", $this->body_classes).'">';
        echo '<div id="wrapper">';
        $this->echoToTopButton();
        $this->echoSidebar();
        echo '<div id="content-wrapper" class="d-flex flex-column">';
        $this->echoNavbar();
        $this->echoContent();
        echo '</div>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
        $this->echoTranslations();
        $this->echoJSCodes();
        $this->echoCSSCodes();
    }
    
    public function import_view($view_name) {
        require __DIR__."/../views/$view_name.php";
    }
    
    public function printMessages() {
        $types = [
            "alert-danger",
            "alert-success",
            "alert-warning",
            "alert-info"
        ];
        if(isset($_SESSION["messages"])){
            foreach ($types as $type){
                if(isset($_SESSION["messages"][$type])){
                    foreach ($_SESSION["messages"][$type] as $key => $message) {
                        $this->printMessage($type, $message);
                        unset($_SESSION["messages"][$type][$key]);
                    }
                }
            }
        }
    }

    public function create_warning_message(string $message, string $type = "alert-danger"){
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
    
    public function printMessage($type, $message) {
        echo "<div class=\"alert $type \">
            <strong>". Utils::get_message_header($type)."</strong> {$message}
        </div>";
    }
    
    protected function preprocessPage(){}
    abstract protected function echoContent();
    
    protected function add_js_files($js_file_path){
        if(is_array($js_file_path)){
            $this->js_files = array_merge($this->css_files, $js_file_path);
        }else{
            $this->js_files[] = $js_file_path;
        }
    }

    public function add_js(string $js_code){
        if(!$this->js_codes){
            $this->js_codes = [];
        }
        $this->js_codes[] = $js_code;
    }
    
    protected function add_css_files($css_file_path){
        if(is_array($css_file_path)){
            $this->css_files = array_merge($this->css_files, $css_file_path);
        }else{
            $this->css_files[] = $css_file_path;
        }
        
    }
    
    protected function add_frontend_translation($translation_key) {
        $this->frontend_translations[$translation_key] = _t($translation_key);
    }

    protected function echoHeader(){ ?>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
            <link rel="icon" href="<?php echo BASE_URL; ?>/assets/favicon.png"/>
            <title> <?php echo $this->title;?> </title>
            <?php foreach ($this->js_files as $js_file) { ?>
            <script src="<?php echo strpos($js_file, "http") !== 0 ? BASE_URL."/$js_file?". hash("MD5", filemtime($js_file)): $js_file;?>"></script>
            <?php } ?>
            <?php foreach ($this->css_files as $css_file) { ?>
                <link rel="stylesheet" href="<?php echo BASE_URL."/$css_file?".hash("MD5", filemtime($css_file));?>"/>
            <?php } ?>
            <script> var root = "<?php echo BASE_URL;?>"; </script>
        </head>
    <?php }
    protected function echoSidebar(){
        echo new Sidebar();
    }
    protected function echoToTopButton(){
        echo "<a class='scroll-to-top rounded' href='#'>
            <i class='fas fa-angle-up'></i>
        </a>";
    }
    protected function echoNavbar(){
        echo new Navbar();
    }
    
    protected function add_default_js_files(){
        $default_js_files = [
            "src/vendor/js/jquery.js",
            "src/vendor/js/jquery-easing.js",
            "src/vendor/js/popper.min.js",
            "src/vendor/js/bootstrap.min.js",
            "src/vendor/js/bootstrap-select.js",
            "src/vendor/js/moment.js",
            "src/vendor/js/bootstrap-datetimepicker.min.js",
            "src/vendor/js/bootstrap-dialog.min.js",
            "src/vendor/js/summernote.js",
            "src/vendor/js/summernote-tr-TR.js",
            "src/vendor/js/sb-admin-2.js",
            "src/js/core.js",
            "src/vendor/js/daterangepicker.min.js",
        ];
        if(class_exists("Translation") && Translation::getLanguage() != "en"){
            $default_js_files[] = "src/vendor/js/bootstrap-select.".Translation::getLanguage().".js";
        }
        $this->js_files = $default_js_files;
    }
    protected function add_default_css_files(){
        $default_css_files = [
            "src/vendor/css/bootstrap.min.css",
            "src/vendor/css/sb-admin-2.css",
            "src/vendor/css/bootstrap-select.min.css",
            "src/vendor/css/bootstrap-datetimepicker.min.css",
            "src/vendor/css/bootstrap-dialog.min.css",
            "src/vendor/css/summernote.css",
            "src/vendor/css/fontawesome/css/all.min.css",
            "src/vendor/css/daterangepicker.css",
            "src/css/core.css"
        ];
        $this->css_files = $default_css_files;
    }
    
    protected function add_default_translations(){
        $this->add_frontend_translation("yes");
        $this->add_frontend_translation("no");
        $this->add_frontend_translation("cancel");
        $this->add_frontend_translation("warning");
        $this->add_frontend_translation("error");
        $this->add_frontend_translation("info");
        $this->add_frontend_translation("ok");
    }

    protected function echoTranslations() {
        echo "<script> var translations = ".json_encode($this->frontend_translations).";"
                . "var language = '".Translation::getLanguage()."';</script>";
    }

    protected function echoJSCodes()
    {
        if(!is_array($this->js_codes)){
            return;
        }
        foreach($this->js_codes as $js_code){
            echo "<script> $js_code </script>";
        }
    }

    protected function echoCSSCodes()
    {
        if(!is_array($this->css_codes)){
            return;
        }
        foreach($this->css_codes as $css_code){
            echo "<style> $css_code </style>";
        }
    }

    public function render()
    {
        $this->printMessages();
    }


    protected function createCsrf(string $form_id) {
        $encryption_key = bin2hex(random_bytes(10));
        $form_build_id = @openssl_encrypt($form_id, self::ENCRYPTION_METHOD,  $encryption_key);
        $_SESSION[$form_build_id] = [
            "encryption_key" => $encryption_key,
            "value" => $this->createFormToken($form_build_id)
        ];
        return $form_build_id;
    }

    protected function getCsrf(string $form_build_id, string $form_id) {
        if(isset($_SESSION[$form_build_id])){
            $encryption_key = $_SESSION[$form_build_id]["encryption_key"];
            $value = $_SESSION[$form_build_id]["value"];

            $decrypted_form_id = openssl_decrypt($form_build_id, self::ENCRYPTION_METHOD, $encryption_key);
            if($form_id != $decrypted_form_id){
                throw new Exception(_t("invalid_key"));
            }
            unset($_SESSION[$form_build_id]);
            return $value;
        }
    }

    protected function createFormToken(string $form_build_id){
        return hash("SHA256", $form_build_id.User::get_user_ip());
    }

    protected function checkCsrfToken($form_id) : bool{
        if($_POST["form_token"] == $this->getCsrf($_POST["form_build_id"], $form_id)){
            return true;
        }else{
            $this->create_warning_message(_t("invalid_operation"));
            return false;
        }
    }
}

