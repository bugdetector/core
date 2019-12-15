<?php
function echo_header(array $js_files, array $css_files, string $title){?>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="icon" href="/logo/housingbritain.png"/>
        <title> <?php echo $title;?> </title>
        <?php foreach ($js_files as $js_file) { ?>
        <script src="<?php echo strpos($js_file, "http") !== 0 ? BASE_URL."/$js_file?". hash("MD5", filemtime($js_file)): $js_file;?>"></script>
        <?php } ?>
        <?php foreach ($css_files as $css_file) { ?>
            <link rel="stylesheet" href="<?php echo BASE_URL."/$css_file?".hash("MD5", filemtime($css_file));?>"/>
        <?php } ?>
        <script> var root = "<?php echo BASE_URL;?>"</script>
    </head>
<?php }