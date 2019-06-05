<?php
function echo_manage_page(ManageController $controller){ ?>
    <div class="container-fluid text-center">    
        <div class="row content">
            <div class="col-sm-3 sidenav">
                  <div class="list-group">
                      <div class="list-group-item tablelist <?php echo $controller->operation == "user" ? "active" : ""; ?> ?>" align="left">
                          <a href="<?php echo SITE_ROOT."/admin/manage/user"; ?>">
                              <span class="glyphicon glyphicon-user"> <?php echo _t(5); ?></span>
                          </a>
                      </div>
                      <div class="list-group-item tablelist <?php echo $controller->operation == "role" ? "active" : ""; ?>" align="left">
                          <a href="<?php echo SITE_ROOT."/admin/manage/role"; ?>">
                              <span class="glyphicon glyphicon-tag"> <?php echo _t(6); ?></span>
                          </a>
                      </div>
                      <div class="list-group-item tablelist <?php echo $controller->operation == "translation" ? "active" : ""; ?>" align="left">
                          <a href="<?php echo SITE_ROOT."/admin/manage/translation"; ?>">
                              <span class="glyphicon glyphicon-globe"> <?php echo _t(100); ?></span>
                          </a>
                      </div>
                      <div  class="manage-controls" id="user-controls" <?php echo $controller->operation != "user" ? "hidden" : ""; ?>>
                          <div class="list-group-item">
                            <a href="#" class="btn btn-info form-control user-logins"><span class="glyphicon glyphicon-eye-open"></span> <?php echo _t(7); ?></a>
                          </div>
                          <div class="list-group-item">
                            <a href="<?php echo SITE_ROOT."/admin/user?q=add" ?>" class="btn btn-success form-control"><span class="glyphicon glyphicon-plus"></span> <?php echo _t(8); ?></a>
                          </div>
                          <div class="list-group-item">
                            <a href="#" class="btn btn-warning form-control edit-user"><span class="glyphicon glyphicon-wrench"></span> <?php echo _t(9); ?></a>
                          </div>
                          <div class="list-group-item">
                            <a href="#" class="btn btn-danger form-control delete-user"><span class="glyphicon glyphicon-remove"></span> <?php echo _t(10); ?></a>
                          </div>
                      </div>
                      <div  class="manage-controls" id="role-controls" <?php echo $controller->operation != "role" ? "hidden" : ""; ?>>
                          <div class="list-group-item">
                            <a href="#add_role_modal" data-toggle="modal" class="btn btn-success form-control add-role"><span class="glyphicon glyphicon-plus"></span> <?php echo _t(11); ?></a>
                          </div>
                          <div class="list-group-item">
                            <a href="#" class="btn btn-danger form-control remove-role"><span class="glyphicon glyphicon-remove"></span> <?php echo _t(12); ?></a>
                          </div>
                      </div>
                      <div  class="manage-controls" id="role-controls" <?php echo $controller->operation != "translation" ? "hidden" : ""; ?>>
                          <div class="list-group-item">
                            <a href="#" class="btn btn-default form-control lang-imp"><span class="glyphicon glyphicon-import"></span> <?php echo _t(101); ?></a>
                          </div>
                          <div class="list-group-item">
                            <a href="#" class="btn btn-default form-control lang-exp"><span class="glyphicon glyphicon-export"></span> <?php echo _t(102); ?></a>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="col-sm-9">
                  <div class="form-group input-group search-group">
                        <input type="text" class="form-control search-field" placeholder="Arama Yapın"/>
                        <span class="input-group-btn">
                            <button class="btn btn-info" type="button"><span class="glyphicon glyphicon-search"></span></button>
                        </span>
                  </div>
              </div>
              <div class="col-sm-9 text-left scroll" id="main_content">
                  <?php 
                    if($controller->operation){
                        $controller->import_view("table_view");
                        echo_table($controller->table_headers, $controller->table_content, "<input type='radio' name='chosen'/>");
                    }
                  ?>
              </div>
        </div>
        <div class="row">
            <?php 
            if($controller->operation){
                $controller->import_view("pagination");
                echo_pagination_view(BASE_URL."/admin/manage/{$controller->operation}?", $controller->page, count($controller->table_content));
            }
            ?>
        </div>
      </div>

<?php }