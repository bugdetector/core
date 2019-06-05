<?php 
function echo_reset_password_page(Reset_passwordController $controller) { ?>
<div class="container-fluid">
    
    <div class="row">
        <div class="col-sm-4"></div>
        <div class="col-sm-4">
            <form method="POST" role="form" style="margin-top: 100px">
                <h3 class="text-center"><?php echo _t(73); ?></h3>
                <input name="PASSWORD" type="password" class="form-control" placeholder="<?php echo _t(22); ?>" required autofocus/>
                <input name="PASSWORD2" type="password" class="form-control" placeholder="<?php echo _t(40); ?>" required/>
                <button class="btn btn-lg btn-warning btn-block" type="submit" ><?php echo _t(73); ?></button>
                <a href="<?php echo SITE_ROOT."/login"; ?>" class="btn btn-lg btn-info btn-block"><?php echo _t(21); ?></a>
            </form>
            <?php $controller->printMessages(); ?>
        </div>
    </div>
</div>

<?php } ?>