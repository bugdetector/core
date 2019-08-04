<?php
$posts = Content::getContentList("", 10, " created DESC");
?>
<div class="list-group">
    <h3>
    <?php 
    echo _t(111);
    foreach ($posts as $post){?>
        </h3>
        <div class="list-group-item" align="left">
        <?php echo "<li><a href='{$post->url_alias}'>{$post->title}</a></li>"; ?>
        </div>
    <?php } ?>
</div>