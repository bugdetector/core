<?php
/**
 * /admin/update
 *
 * @author murat
 */
class AdminManageUpdateController extends AdminManageController{
    public function check_access() : bool {
        if(!isset($_SESSION["install_key"])){
            return parent::check_access();
        } else {
           return true;
        }
    }
    
    protected function add_default_translations(){
        if(!isset($_SESSION["install_key"])){
            parent::add_default_translations();
        }
    }
    
    public function echoNavbar() {
        if(!isset($_SESSION["install_key"])){
            parent::echoNavbar();
        }
    }

    public function echoSidebar() {
        if(!isset($_SESSION["install_key"])){
            parent::echoSidebar();
        }
    }
    
    protected function echoTranslations() {
        if(!isset($_SESSION["install_key"])){
            parent::echoTranslations();
        }
    }
    
    private $content, $updates;
    protected function preprocessPage() {
        try{
            $title = _t("updates");
            $success = _t("update_success");
        }catch(Exception $ex){
            $title = "Updates";
            $success = "Installed successfuly.";
        }
        $this->setTitle($title);
        $this->updates = Migration::getUpdates();
        if(isset($_POST["update"])){
            Migration::update();
            $this->updates = Migration::getUpdates();
            $this->create_warning_message($success, "alert-success");
            if(isset($_SESSION["install_key"])){
                unset($_SESSION["install_key"]);
                Utils::core_go_to(BASE_URL."/admin/manage/update");
            }
        }
        $this->operation = "update";
        $this->table_headers = ["<b>$title</b>"];
        $this->table_content = array_map(function($el){
            return [basename($el, ".php")];
        }, $this->updates);
        if(!empty($this->updates)){
            $this->action_section = $this->getForm();
        }
    }
    
    protected function echoContent() {
        if(!isset($_SESSION["install_key"])){
            parent::echoContent();
        } else if(isset ($_GET["key"]) && $_GET["key"] == $_SESSION["install_key"]) {
            echo $this->getForm();
        }
    }
    
    public function getForm(){
        $form = new FormBuilder("post");
        try{
            $no_update = _t("no_update");
            $available_version = _t("available_version");
        }catch(Exception $ex){
            $no_update = "There is no update.";
            $available_version = "System will update to version";
        }
        if(empty($this->updates)){
            $input = new InputField("ok");
            $input->setType("submit")->setLabel($no_update)->addClass("d-none");
            $form->addField($input);
        } else {
            $input = new InputField("update");
            $input->setType("submit")
                    ->addClass("btn btn-sm btn-primary shadow-sm")
                    ->setValue(VERSION ? "Update" : "Install ".SITE_NAME);
            if(!VERSION){
                $form->addClass("container justify-content-center align-items-center");
                $input->setLabel("$available_version: ". basename(max($this->updates), ".php"));
            }else{
                $input->setLabel("$available_version: ". basename(max($this->updates), ".php"));
            }
            $form->addField($input);
        }
        return $form;
    }
}