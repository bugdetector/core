

<div class="col-md-3 sidenav">
    <a href="<?php echo SITE_ROOT;?>/admin/table/new" class="btn btn-success form-control" align="left">
        <span class="glyphicon glyphicon-hdd"></span> <?php echo _t(13); ?>
    </a>
    <div class="form-group input-group search-group">
        <input type="text" class="form-control table-search-field" placeholder="<?php echo mb_strtoupper(_t(1)); ?>"/>
        <span class="input-group-btn">
            <button class="btn btn-info" type="button"><span class="glyphicon glyphicon-search"></span></button>
        </span>
    </div>
    <div class="list-group">
    <?php
        $tables = get_information_scheme();
        foreach ($tables as $document){
    ?>
        <div class="list-group-item tablelist text-left">
            <a href="<?php echo SITE_ROOT."/admin/table/$document"; ?>">
                <span class="glyphicon glyphicon-list-alt"></span><?php echo $document; ?>
            </a>
            
            <div class="dropdown float-right">
                <a href="#" title="<?php echo _t(17); ?>" id="openmenu" class="dropdown-toogle" data-toggle="dropdown"/>
                    <span class="glyphicon glyphicon-option-vertical" ></span> </a>
                <div class="dropdown-menu dropdown-menu-left">
                    <a href="<?php echo BASE_URL."/admin/insert/$document"; ?>"><label class="form-control dropdown-item rowadd core-control"><span class="glyphicon glyphicon-floppy-disk"></span><?php echo _t(14); ?></label></a>
                    <a href="<?php echo BASE_URL."/admin/table/new/$document"; ?>"><label class="form-control dropdown-item tableadd core-control"><span class="glyphicon glyphicon-plus"></span><?php echo _t(15); ?></label></a>
                    <label class="form-control dropdown-item tabletruncate core-control"><span class="glyphicon glyphicon-trash"></span><?php echo _t(108); ?></label>
                    <label class="form-control dropdown-item tabledrop core-control"><span class="glyphicon glyphicon-remove"></span><?php echo _t(16); ?></label>
                </div>
            </div>
        </div>
    <?php } ?>
    </div>
</div>