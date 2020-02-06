<nav class="navbar navbar-dark">
  <div class="container navbar navbar-expand-md">
    <a class="navbar-brand" href="<?php echo SITE_ROOT."/admin"; ?>"><img src="<?php echo SITE_ROOT."/assets/Core_logo.png";?>"/></a>
    <div class="navbar-header">
      <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#coreNavbar">
        <span class="navbar-toggler-icon"></span>                      
      </button>
    </div>
    <div class="collapse navbar-collapse justify-content-between" id="coreNavbar">
      <ul class="nav navbar-nav">
          
      </ul>
      <ul class="nav navbar-nav">
          <?php if(!User::get_current_core_user()->isLoggedIn()){ ?>
                <li class="nav-item">
                  <a href="<?php echo SITE_ROOT."/login/"; ?>" class="nav-link"><span class="glyphicon glyphicon-user"></span><?php echo _t(115); ?></a>
                </li>
          <?php }else if(User::get_current_core_user()->isAdmin()) { ?>
                <li class="nav-item">
                  <a href="<?php echo SITE_ROOT."/admin/"; ?>" class="nav-link"><span class="glyphicon glyphicon-user"></span><?php echo User::get_current_core_user()->NAME; ?></a>
                </li>
          <?php }else{ ?>
                <li class="nav-item">
                  <a href="<?php echo SITE_ROOT."/"; ?>" class="nav-link"><span class="glyphicon glyphicon-user"></span><?php echo User::get_current_core_user()->NAME; ?></a>
                </li>
          <?php }
            if(User::get_current_core_user()->isLoggedIn()){ ?>
            <li class="nav-item">
              <a href="<?php echo SITE_ROOT."/logout"; ?>" id="logout" class="nav-link"><span class="glyphicon glyphicon-log-out"></span> <?php echo _t(4); ?></a>
            </li>
            <?php } ?>
      </ul>
    </div>
  </div>
</nav>