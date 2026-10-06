<?php

/**
 * Used by docker/entrypoint.sh.
 * Prints "installed" or "empty".
 * Exits with 1 when the database is not reachable (worth retrying).
 * Any other error, such as an invalid environment, exits with 255.
 */

require __DIR__ . "/../bootstrap.php";

try {
    echo isset(CoreDB::database()->getTableList()["users"]) ? "installed" : "empty";
} catch (PDOException $ex) {
    fwrite(STDERR, $ex->getMessage() . PHP_EOL);
    exit(1);
}
