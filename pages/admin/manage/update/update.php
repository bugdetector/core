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
    
    protected function echoFooter() {
        if(!isset($_SESSION["install_key"])){
            parent::echoFooter();
        }
    }
    
    protected function echoTranslations() {
        if(!isset($_SESSION["install_key"])){
            parent::echoTranslations();
        }
    }
    
    private $content, $updates;
    protected function preprocessPage() {
        $this->updates = Migration::getUpdates();
        if(isset($_POST["update"])){
            Migration::update();
            $this->updates = Migration::getUpdates();
            Utils::create_warning_message("Updated successfuly.", "alert-success");
            if(isset($_SESSION["install_key"])){
                unset($_SESSION["install_key"]);
                core_go_to(BASE_URL."/admin/manage/update");
            }
        }
        $this->operation = "update";
        $this->table_headers = ["<b>Updates</b>"];
        $this->table_content = array_map(function($el){
            return [basename($el, ".php")];
        }, $this->updates);
    }
    
    protected function echoContent() {
        if(!isset($_SESSION["install_key"])){
            parent::echoContent();
        } else if(isset ($_GET["key"]) && $_GET["key"] == $_SESSION["install_key"]) {
            $this->echoForm();
        }
    }
    
    public function echoForm(){
        $form = new FormBuilder("post");
        if(empty($this->updates)){
            $input = new InputField("ok");
            $input->setType("submit")->setLabel("There is no update.")->addClass("hidden");
            $form->addField($input);
        } else {
            $input = new InputField("update");
            $input->setType("submit")
                    ->addClass("btn btn-primary")
                    ->setLabel("System will update to version: ". basename(max($this->updates), ".php"))
                    ->setValue("Update");
            $form->addField($input);
        }
        $this->content = $form;
        echo $form->renderField();
    }
}