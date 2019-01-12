<?php
/**
 * Commands for the user of the application
 *
 * These commands help to manage the application and data.
 * They are provided by modules.
 */
require (__DIR__.'/application.init.php');

\Jelix\Scripts\ModulesCommands::run();

