<?php if($select){ ?>
<script>
    $(document).ready(function (){
       control_table = "<?php echo $this->arguments[0];?>";
       var result = <?php echo json_encode($select); ?>;
       var table = array_to_table(result.values, result.skeleton);
        $("#main_content").html("");
        $("#main_content").append(table);
        $(".list-group-item.tablelist a:textEquals('<?php echo $this->arguments[0];?>')").parent().addClass("active");
    });
</script>
<?php }?>
<div class="container-fluid text-center">    
    <div class="row content">
     <?php $this->import_view("sidebar_table_list");?>
      <div class="col-sm-9">
          <div class="row">
              <div class="form-group input-group search-group">
                  <input type="text" class="form-control search-field" placeholder="<?php echo _t(55); ?>"/>
                    <span class="input-group-btn">
                        <button class="btn btn-info btn-search" type="button"><span class="glyphicon glyphicon-search"></span></button>
                    </span>
              </div>
          </div>
          <div class="row">
              <div class="col-sm-12 scroll text-left" id="main_content">
                <?php $this->printMessages(); ?>
              </div>
          </div>
          <div class="row">
              <div class="col-sm-12" id="pagination">
                  <div class="text-right">
                   <?php 
                   if($select["count"] > 0){
                        $last_index = ($offset+PAGE_SIZE_LIMIT) > $select["count"] ? $select["count"] :($offset+PAGE_SIZE_LIMIT);
                        echo _t(94, [$select["count"], ($offset+1), $last_index ]); 
                   }?>
                  </div>
              </div>
          </div>
          <?php if($select){ ?>
          <div class="row">
              <?php
              $this->import_view("pagination");
              echo_pagination_view($query_link, $page, $select["count"]);
              ?>
          </div>
          <?php } ?>
      </div>
    </div>
  </div>