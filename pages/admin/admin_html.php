<div class="container container-fluid text-center content">    
  <div class="row row-eq-height">
      <div class="col-sm-4 card p-3">
        <a href="<?php echo BASE_URL."/admin/manage/user"; ?>">
          <h4><span class="glyphicon glyphicon-user"></span></h4>
          <h4><?php echo _t(122).": ".$this->number_of_members; ?></h4>
        </a>
      </div>
      <div class="col-sm-4 card p-3">
        <a href="<?php echo BASE_URL."/admin/manage/update"; ?>">
          <h4><span class="glyphicon glyphicon-circle-arrow-up"></span></h4>
          <h4><?php echo _t(123).": ".VERSION; ?></h4>
          <span><?php
          $updates = Migration::getUpdates(); 
          echo empty($updates) ? _t(124) : _t(125).": ".end($updates); ?></span>
        </a>
      </div>
  </div>
</div>