<?php 
function echo_login_page(LoginController $controller) { ?>
<div class="container-fluid">
    
    <div class="row">
        <div class="col-sm-4"></div>
        <div class="col-sm-4">
            <form id="loginForm" method="POST" role="form" style="margin-top: 100px">
                <input type="text" class="hidden" name="form_build_id" value="<?php echo $controller->form_build_id; ?>"/>
                <h3 class="text-center"><?php echo _t(21); ?></h3>
                <input name="username" class="form-control" placeholder="<?php echo _t(20); ?>" required autofocus>
                <input name="password" class="form-control" type="password" placeholder="<?php echo _t(22); ?>" required>
                <input type="submit" class="btn btn-lg btn-primary btn-block" value="<?php echo _t(21); ?>" name="login">
                <a href="<?php echo SITE_ROOT."/forget_password"; ?>" class="btn btn-lg btn-info btn-block" id="forgetPassword">
                    <?php echo _t(23); ?></a>
            </form>
            <?php $controller->printMessages(); ?>
        </div>
    </div>
</div>

<?php } ?>