<?php function echo_mainpage(MainpageController $controller) { ?>

<div class="container-fluid text-center">    
  <div class="row content">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <?php  
        $controller->import_view("searchinput");             
        echo_search_input($controller->form_build_id); 
        ?>
    </div>
  </div>
  <div class="row content">
    
        <?php if($controller->results){ 
                 foreach ($controller->results as $result) {?>
                    <div class="col-sm-3">"
                        <div class="card">
                            <div class="card-container text-left"">
                               <a href="<?php echo BASE_URL."/blog/{$result->url_alias}"; ?>">
                                <img src="<?php echo $result->getImageLink(); ?>"/>
                                <h2><?php echo $result->title; ?></h2>
                               </a>
                               <p><?php echo substr($result->body, 0, 255).(strlen($result->body) > 255 ? "..." : ""); ?></p>
                           </div>
                         </div> 
                    </div>
                 <?php }
        
                 } ?>
  </div>  
</div>

<?php }