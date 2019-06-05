<?php 
function echo_forget_password_page(Forget_passwordController $controller) { ?>
<div class="container-fluid">
    
    <div class="row">
        <div class="col-sm-4"></div>
        <div class="col-sm-4">
            <form method="POST" role="form" style="margin-top: 100px">
                <h3 class="text-center"><?php echo _t(73); ?></h3>
                <input name="username" class="form-control" placeholder="<?php echo _t(20); ?>" required autofocus/>
                <input name="email" class="form-control" type="email" placeholder="<?php echo _t(35); ?>" required/>
                <button class="btn btn-lg btn-warning btn-block" type="submit" ><?php echo _t(75); ?></button>
                <a href="<?php echo SITE_ROOT."/login"; ?>" class="btn btn-lg btn-info btn-block"><?php echo _t(21); ?></a>
            </form>
            <?php $controller->printMessages(); ?>
        </div>
    </div>
</div>

<?php } ?>