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
          <li class="nav-item">
            <a href="<?php echo SITE_ROOT."/admin/table"; ?>" class="nav-link"><span class="glyphicon glyphicon-th-list"></span> <?php echo _t(1); ?></a>
          </li>
          <li class="nav-item">
            <a href="<?php echo SITE_ROOT."/admin/manage"; ?>" class="nav-link"><span class="glyphicon glyphicon-cog"></span> <?php echo _t(2); ?></a>
          </li>
      </ul>
      <ul class="nav navbar-nav">
          <li class="nav-item">
            <a href="<?php echo SITE_ROOT."/admin/user"; ?>" class="nav-link"><span class="glyphicon glyphicon-user"></span><?php $user = User::get_current_core_user(); echo $user->isLoggedIn() ? "$user->NAME $user->SURNAME": _t(115); ?></a>
          </li>
          <li class="nav-item">
            <a href="<?php echo SITE_ROOT."/logout"; ?>" id="logout" class="nav-link"><span class="glyphicon glyphicon-log-out"></span> <?php echo _t(4); ?></a>
          </li>
      </ul>
    </div>
  </div>
</nav>

