<?php function echo_mainpage(MainpageController $controller) { ?>

<div class="container-fluid text-center">    
  <div class="row content">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <form method="POST" class="input-group">
            <input type="text" name="search_param" class="form-control" placeholder="<?php echo _t(55); ?>"/>
            <span class="input-group-btn">
                <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>
            </span>
        </form>
    </div>
  </div>
  <div class="row content">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
    </div>
  </div>  
</div>

<?php }