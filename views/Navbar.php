<?php

class Navbar extends View
{
  public function render()
  { ?>
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

      <!-- Navbar -->
      <ul class="navbar-nav ml-auto d-flex justify-content-between w-100">
        <li class="nav-item">
          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3 mt-3">
            <i class="fa fa-bars"></i>
          </button>
        </li>
        <li class="nav-item">
          <a class="navbar-brand" href="<?php echo BASE_URL; ?>"></a>
        </li>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo User::get_current_core_user()->getFullName(); ?></span>
            <img class="img-profile rounded-circle" src="<?php echo BASE_URL . "/assets/default-profile-picture.png"; ?>">
          </a>
          <?php if (User::get_current_core_user()->isLoggedIn()) : ?>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
              <a class="dropdown-item" href="<?php echo BASE_URL . "/admin/user"; ?>">
                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                <?php echo _t("profile"); ?>
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="<?php echo BASE_URL . "/logout"; ?>">
                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                <?php echo _t("logout"); ?>
              </a>
            </div>
          <?php else : ?>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
              <a class="dropdown-item" href="<?php echo BASE_URL . "/login"; ?>">
                <i class="fas fa-sign-in-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                <?php echo _t("login"); ?>
              </a>
            </div>
          <?php endif; ?>
        </li>


      </ul>

    </nav>
    <!-- Navbar -->

<?php }
}
