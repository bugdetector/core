<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . "/../bootstrap.php";

$app = new Application();
$commands = Yaml::parseFile(__DIR__ . "/../config/commands.yml");
foreach ($commands as $command) {
    $app->add(new $command());
}

$app->run();
