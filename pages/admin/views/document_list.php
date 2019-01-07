

<div class="col-sm-3 sidenav">
    <?php if( get_current_core_user()->isAdmin() ) {
        ?>
    <a href="<?php echo SITE_ROOT;?>/admin/manual/new" class="btn btn-success form-control" align="left">
        <span class="glyphicon glyphicon-folder-open"></span> Yeni Doküman Ekle
    </a>
    <?php } ?>
    <div class="form-group input-group search-group">
        <input type="text" class="form-control table-search-field" placeholder="Döküman ara"/>
        <span class="input-group-btn">
            <button class="btn btn-info" type="button"><span class="glyphicon glyphicon-search"></span></button>
        </span>
    </div>
    <div class="list-group">
    <?php
        $documents = db_select(DOCUMENTS)->select("", ["ID","BASLIK"])->execute()->fetchAll(PDO::FETCH_NUM);
        foreach ($documents as $document){
    ?>
        <div class="list-group-item tablelist" align="left">
            <a href="<?php echo SITE_ROOT."/admin/manual/$document[0]"; ?>">
                <span class="glyphicon glyphicon-briefcase"></span><?php echo $document[1]; ?>
            </a>
            <div class="dropdown pull-right show">
                <a href="#" title="Seçenekler" id="openmenu" class="dropdown-toogle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="glyphicon glyphicon-option-vertical" ></span> </a>
                <div class="dropdown-menu" aria-labelledby="openmenu">
                    <a href="<?php echo SITE_ROOT."/admin/manual/new/$document[0]";?>" class="dropdown-item core-control"><span class="glyphicon glyphicon-floppy-save"></span> Dökümanı Güncelle</a>
                    <a href="#" class="dropdown-item core-control remove_document" data-bind="<?php echo $document[0]; ?>"><span class="glyphicon glyphicon-remove"></span> Dökümanı Sil</a>
                </div>
            </div>
        </div>
        
    <?php } ?>
    </div>
</div>
