<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Laurent Jouanneau
* @contributor
* @copyright 2007-2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require ('../application.init.php');

require (JELIX_LIB_PATH.'checker.php');

\Jelix\Installer\Checker\CheckerPage::show();

