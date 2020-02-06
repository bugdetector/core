<div class="container-fluid text-center content">    
    <div class="row content">
        <div class="col-sm-3 sidenav">
              <div class="list-group">
                  <div class="list-group-item tablelist <?php echo $this->operation == "user" ? "active" : ""; ?> ?>" align="left">
                      <a href="<?php echo SITE_ROOT."/admin/manage/user"; ?>">
                          <span class="glyphicon glyphicon-user"> </span>
                          <?php echo _t(5); ?>
                      </a>
                  </div>
                  <div class="list-group-item tablelist <?php echo $this->operation == "role" ? "active" : ""; ?>" align="left">
                      <a href="<?php echo SITE_ROOT."/admin/manage/role"; ?>">
                          <span class="glyphicon glyphicon-tag"> </span>
                          <?php echo _t(6); ?>
                      </a>
                  </div>
                  <div class="list-group-item tablelist <?php echo $this->operation == "translation" ? "active" : ""; ?>" align="left">
                      <a href="<?php echo SITE_ROOT."/admin/manage/translation"; ?>">
                          <span class="glyphicon glyphicon-globe"> </span>
                          <?php echo _t(100); ?>
                      </a>
                  </div>
                  <div class="list-group-item tablelist <?php echo $this->operation == "update" ? "active" : ""; ?>" align="left">
                      <a href="<?php echo SITE_ROOT."/admin/manage/update"; ?>">
                          <span class="glyphicon glyphicon-cloud-upload"> </span>
                          Updates
                      </a>
                  </div>
                  <div  class="manage-controls" id="user-controls" <?php echo $this->operation != "user" ? "hidden" : ""; ?>>
                      <div class="list-group-item">
                        <a href="<?php echo SITE_ROOT."/admin/user?q=add" ?>" class="btn btn-outline-primary form-control"><span class="glyphicon glyphicon-plus"></span> <?php echo _t(8); ?></a>
                      </div>
                  </div>
                  <div  class="manage-controls" id="role-controls" <?php echo $this->operation != "role" ? "hidden" : ""; ?>>
                      <div class="list-group-item">
                        <a href="" class="btn btn-outline-primary form-control add-role"><span class="glyphicon glyphicon-plus"></span> <?php echo _t(11); ?></a>
                      </div>
                  </div>
                  <div  class="manage-controls" id="role-controls" <?php echo $this->operation != "translation" ? "hidden" : ""; ?>>
                      <div class="list-group-item">
                        <a href="#" class="btn btn-outline-primary form-control lang-imp"><span class="glyphicon glyphicon-import"></span> <?php echo _t(101); ?></a>
                      </div>
                      <div class="list-group-item">
                        <a href="#" class="btn btn-outline-primary form-control lang-exp"><span class="glyphicon glyphicon-export"></span> <?php echo _t(102); ?></a>
                      </div>
                  </div>
              </div>
          </div>
          <div class="col-sm-9">
            <div class="col-sm-12">
                <?php $this->printMessages(); ?>
            </div>
            <div class="col-sm-12 text-left scroll" id="main_content">
                <?php 
                    $this->import_view("table_view");
                    if(get_class($this) == "AdminManageUpdateController"){
                        echo "<div class='col-sm-6'>";
                        echo_table($this->table_headers, $this->table_content);
                        echo "<div>";
                        $this->echoForm();
                    }else if($this->operation){
                        echo_table($this->table_headers, $this->table_content,[
                            "orderable" => true,
                            "filter_options" => $this->filter_options
                        ]);
                    }
                ?>
            </div>
            <div class="col-sm-12">
            <?php 
                if($this->operation && get_class($this) == "AdminManageController"){
                    $this->import_view("pagination");
                    echo_pagination_view(BASE_URL."/admin/manage/{$this->operation}?", $this->page, $this->entry_count);
                }
                ?>
            </div>
        </div>
    </div>
</div>