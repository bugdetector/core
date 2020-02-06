<div class="container-fluid text-center">    
    <div class="row content">
     <?php $this->import_view("sidebar_table_list");?>
      <div class="col-md-9">
          <div class="row">
              <div class="col-md-12 scroll text-left" id="main_content">
                <?php 
                    $this->printMessages();
                    echo_table($this->table_header ? : [], $this->table_content, [
                        "orderable" => true,
                        "filter_options" =>$this->filter_options
                    ]);
                ?>
              </div>
          </div>
          <div class="row">
              <div class="col-md-12" id="pagination">
                  <div class="text-right">
                   <?php 
                   if($this->total_count > 0){
                        echo _t(94, [$this->total_count, ($this->offset+1), $this->offset+count($this->table_content) ]); 
                   }?>
                  </div>
              </div>
          </div>
          <?php if($this->table){ ?>
          <div class="row">
              <?php
              $this->import_view("pagination");
              echo_pagination_view($this->query_link, $this->page, $this->total_count);
              ?>
          </div>
          <?php } ?>
      </div>
    </div>
  </div>