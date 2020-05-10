<?php

class Sidebar extends View
{
    public function render(){
        $user = User::get_current_core_user(); ?>

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled position-sticky" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL; ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="<?php echo BASE_URL . "/assets/favicon.png"; ?>" style="max-width: 30px;" />
                </div>
                <div class="sidebar-brand-text mx-3"><img class="w-100" src="<?php echo BASE_URL . "/assets/logo.png"; ?>" /></div>
            </a>
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Dashboard -->
            <?php if ($user->isAdmin()) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL . "/admin"; ?>">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span><?php echo _t("dashboard"); ?></span>
                    </a>
                </li>

                <!-- Nav Item - Pages Collapse Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse_management" aria-expanded="true" aria-controls="collapse_management">
                        <i class="fas fa-fw fa-cog"></i>
                        <span><?php echo _t("management"); ?></span>
                    </a>
                    <div id="collapse_management" class="collapse">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header"><?php echo _t("management"); ?></h6>
                            <a class="collapse-item" href="<?php echo BASE_URL . "/admin/manage/user"; ?>"><?php echo _t("user_management"); ?></a>
                            <a class="collapse-item" href="<?php echo BASE_URL . "/admin/manage/role"; ?>"><?php echo _t("role_management"); ?></a>
                            <a class="collapse-item" href="<?php echo BASE_URL . "/admin/manage/translation"; ?>"><?php echo _t("translations"); ?></a>
                            <a class="collapse-item" href="<?php echo BASE_URL . "/admin/manage/update"; ?>"><?php echo _t("updates"); ?></a>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL . "/admin/table"; ?>">
                        <i class="fas fa-fw fa-chart-area"></i>
                        <span><?php echo _t("tables"); ?></span>
                    </a>
                </li>
            <?php } ?>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">
            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

<?php }
}
