<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      
        <a class="navbar-brand" href="<?php echo SITE_ROOT."/"; ?>"><img src="<?php echo SITE_ROOT."/assets/Core_logo.png";?>"/></a>
    </div>
    <div class="collapse navbar-collapse" id="coreNavbar">
      <ul class="nav navbar-nav">
          
      </ul>
      <ul class="nav navbar-nav navbar-right">
          <?php if(!get_current_core_user()->isLoggedIn()){ ?>
                <li><a href="<?php echo SITE_ROOT."/login/"; ?>"><span class="glyphicon glyphicon-user"></span><?php echo _t(115); ?></a></li>
          <?php }else if(get_current_core_user()->isAdmin()) { ?>
                <li><a href="<?php echo SITE_ROOT."/admin/"; ?>"><span class="glyphicon glyphicon-user"></span><?php echo get_current_core_user()->NAME; ?></a></li>
          <?php }else{ ?>
                <li><a href="<?php echo SITE_ROOT."/"; ?>"><span class="glyphicon glyphicon-user"></span><?php echo get_current_core_user()->NAME; ?></a></li>
          <?php }
            if(get_current_core_user()->isLoggedIn()){ ?>
            <li><a href="<?php echo SITE_ROOT."/logout"; ?>" id="logout"><span class="glyphicon glyphicon-log-out"></span> <?php echo _t(4); ?></a></li>
            <?php } ?>        
      </ul>
    </div>
  </div>
</nav>

