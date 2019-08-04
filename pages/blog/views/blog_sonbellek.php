<?php

function echo_blog_page(BlogController $controller){
    ?>
<div class="container-fluid">    
  <div class="row content">
      <div class="col-sm-3">
          <div class="row">
              <div class="col-sm-12">
                  <?php $controller->import_view("last_posts") ?>
              </div>
              <div class="col-sm-12">
                  <?php //$controller->import_view("popular_posts") ?>
              </div>
          </div>
      </div>
    <div class="col-sm-9">
        <div class="row">
            <img src='<?php echo $controller->content->getImageLink(); ?>'>
        </div>
        <div class="row">
            <?php echo $controller->content->body; ?>
        </div>
    </div>
  </div>
</div>

<?php
}