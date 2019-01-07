<div class="modal fade" id="add_role_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-info">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title"><?php echo _t(11) ?></h4>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="row ">
                        <div class="col-sm-3">
                            <label><?php echo _t(50) ?></label>
                        </div>
                        <div class="col-sm-9">
                            <input class="form-control uppercase_filter" type="text" name="ROLE"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-primary" value="<?php echo _t(14) ?>"/>
                </div>                    
            </form>
        </div>
      
    </div>
  </div>