<?php
/**
 * Include for scripts/runtests.php of jelix application.
 * It allows to run tests from `tests/` directory of modules.
 *
 * This file and scripts/runtests.php are deprecated.
 * Prefer to store your tests class outside module, as usual for any
 * PHPunit tests suite.
 *
 * @deprecated
 */

jApp::setEnv('phpunit');

if (strpos('/usr/bin/php', '@php_bin') === 0) {
    set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
}

$currentDir = __DIR__.DIRECTORY_SEPARATOR;
require_once ($currentDir.'classes/command.php');

jelix_TextUI_Command::main();
