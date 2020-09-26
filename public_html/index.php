<?php

define("SITE_ROOT", substr(str_replace(basename(__FILE__), "", $_SERVER["SCRIPT_NAME"]), 0, -1));
require "../bootstrap.php";
