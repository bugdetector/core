<?php 
/**
 * @var Forget_passwordController $this
 */
?>
<div class="container">
    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-2"><?php echo _t("forgot_password_question"); ?>?</h1>
                                    <p class="mb-4"></p>
                                    <?php $this->printMessages(); ?>
                                </div>
                                <form method="POST" class="user">
                                    <div class="form-group">
                                        <input name="username" class="form-control form-control-user" id="exampleInputName" aria-describedby="nameHelp" placeholder="<?php echo _t("username"); ?>" required autofocus />
                                    </div>
                                    <div class="form-group">
                                        <input name="email" type="email" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="<?php echo _t("email"); ?>" required />
                                    </div>
                                    <button class="btn btn-warning btn-user btn-block" name="reset" type="submit"><?php echo _t("reset"); ?></button>
                                    <input type="hidden" name="form_build_id" value="<?php echo $this->form_build_id; ?>"/>
                                    <input type="hidden" name="form_token" value="<?php echo $this->form_token; ?>"/>
                                </form>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?php echo SITE_ROOT . "/login"; ?>"><?php echo _t("login"); ?>!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>