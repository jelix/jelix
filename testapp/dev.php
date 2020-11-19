<?php
/**
 * @package  jelix
 * @author   Laurent Jouanneau
 * @contributor
 * @copyright 2011-2018 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */
use Jelix\DevHelper\JelixCommands;

require (__DIR__.'/application.init.php');

// Commands for the developer

$application = JelixCommands::setup();

// here you can add commands to $application


JelixCommands::launch($application);