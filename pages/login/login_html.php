<div class="container-fluid content">
    
    <div class="row">
        <div class="col-sm-4"></div>
        <div class="col-sm-4 text-center">
            <img src="<?php echo SITE_ROOT; ?>/assets/Core_logo.png">
            <form id="loginForm" method="POST" role="form" style="margin-top: 10px">
                <input type="text" class="d-none" name="form_build_id" value="<?php echo $this->form_build_id; ?>"/>
                <input name="username" class="form-control" placeholder="<?php echo _t(20); ?>" required autofocus>
                <input name="password" class="form-control" type="password" placeholder="<?php echo _t(22); ?>" required>
                <input type="submit" class="btn btn-lg btn-outline-info btn-block" value="<?php echo _t(21); ?>" name="login">
                <div class="col-sm-12 login-actions">
                    <label class="float-left">
                        <input type="checkbox" value="remember-me" name="remember-me" <?php if(isset($_COOKIE["remember-me"]) && $_COOKIE["remember-me"]==true ) echo "checked"; ?>>
                        <?php echo _t(112); ?>
                    </label>
                    <label class="float-right">
                    <a href="<?php echo SITE_ROOT."/forget_password"; ?>" id="forgetPassword">
                        <?php echo _t(23); ?></a>
                    </label>
                </div>
            </form>
            <?php $this->printMessages(); ?>
        </div>
    </div>
</div>