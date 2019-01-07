<?php function echo_profile_page(Controller $controller) { 
    $user = $controller->user;?>
<div class="container container-fluid">
    <div class="row">
        <?php $controller->printMessages(); ?>
        <form class="container-fluid text-center" method="POST">
            <input type="text" class="hidden" name="form_build_id" value="<?php echo $controller->form_build_id; ?>"/>
            <div class="col-sm-6">
                <div class="row content text-left">
                    <div class="col-sm-12 text-left">
                        <h4><?php echo _t(33); ?></h4>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo _t(20); ?>
                    </div>
                    <div class="col-sm-6 text-left">
                        <input class="form-control" <?php echo $controller->operation === "update" ? "disabled" : ""; ?> type="text" name="user_info[USERNAME]" value="<?php echo $user->USERNAME; ?>"/>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo _t(27); ?>
                    </div>
                    <div class="col-sm-6 text-left">
                        <input class="form-control" type="text" name="user_info[NAME]" value="<?php echo $user->NAME; ?>"/>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo mb_convert_case(_t(28), MB_CASE_TITLE); ?>
                    </div>
                    <div class="col-sm-6 text-left">
                        <input class="form-control" type="text" name="user_info[SURNAME]" value="<?php echo $user->SURNAME; ?>"/>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo _t(35); ?>
                    </div>
                    <div class="col-sm-6 text-left">
                        <input class="form-control" type="text" name="user_info[EMAIL]" value="<?php echo $user->EMAIL; ?>"/>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo mb_convert_case(_t(29), MB_CASE_TITLE); ?>
                    </div>            
                    <div class="col-sm-6 text-left">
                        <input class="form-control" type="text" name="user_info[PHONE]" placeholder="5xxxxxxxxx" value="<?php echo $user->PHONE; ?>"/>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo _t(36); ?>
                    </div>            
                    <div class="col-sm-6 text-left">
                        <select class="selectpicker form-control" multiple name="user_info[ROLES][]">
                            <?php foreach ($controller->current_user_roles as $role){ ?>
                            <option selected><?php echo $role; ?></option>
                            <?php } ?>
                            <?php foreach ($controller->excluded_roles as $role){ ?>
                            <option><?php echo $role; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-12">
                        <input type="submit" name="save" class="btn btn-success form-control" value="<?php echo $controller->operation == "update" ? _t(37) : _t(14); ?>"/> 
                    </div>
                </div>
        </div>
        <?php if($controller->operation == "update"){ ?>
        <div class="col-sm-6">
                <div class="row content text-left">
                    <div class="col-sm-12 text-left">
                        <h4><?php echo _t(38); ?></h4>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo _t(39); ?>
                    </div>

                    <div class="col-sm-6 text-left">
                        <input class="form-control" type="password" name="password[ORIGINAL_PASSWORD]"/>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo _t(22); ?>
                    </div>

                    <div class="col-sm-6 text-left">
                        <input class="form-control" type="password" name="password[PASSWORD]"/>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-6 text-left">
                        <?php echo _t(40); ?>
                    </div>

                    <div class="col-sm-6 text-left">
                        <input class="form-control" type="password" name="password[PASSWORD2]"/>
                    </div>
                </div>
                <div class="row content">
                    <div class="col-sm-12">
                        <input type="submit" name="change_password" class="btn btn-success form-control" value="<?php echo _t(38); ?>"/> 
                    </div>
                </div>
            </div>
        </form>
        <?php } ?>
    </div>
</div>
<?php } 