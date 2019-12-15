<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#coreNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>                        
      </button>
        <a class="navbar-brand" href="<?php echo SITE_ROOT."/admin"; ?>"><img src="<?php echo SITE_ROOT."/assets/Core_logo.png";?>"/></a>
    </div>
    <div class="collapse navbar-collapse" id="coreNavbar">
      <ul class="nav navbar-nav">
          <li><a href="<?php echo SITE_ROOT."/admin/table"; ?>"><?php echo _t(1); ?></a></li>
        <li> </li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if(get_current_core_user()->isAdmin()){
        ?><li><a href="<?php echo SITE_ROOT."/admin/manage"; ?>"><span class="glyphicon glyphicon-cog"></span> <?php echo _t(2); ?></a></li>
            <?php } ?>
        <li><a href="<?php echo SITE_ROOT."/admin/user"; ?>"><span class="glyphicon glyphicon-user"></span><?php $user = get_current_core_user(); echo $user->isLoggedIn() ? "$user->NAME $user->SURNAME": _t(115); ?></a></li>
        <li><a href="<?php echo SITE_ROOT."/logout"; ?>" id="logout"><span class="glyphicon glyphicon-log-out"></span> <?php echo _t(4); ?></a></li>
      </ul>
    </div>
  </div>
</nav>

