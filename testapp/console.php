<?php
/**
 * @author   Laurent Jouanneau
 * @contributor
 * @copyright 2018 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */
require (__DIR__.'/application.init.php');

// Commands for the user of the application
\Jelix\Scripts\ModulesCommands::run();

