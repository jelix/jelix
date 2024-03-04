<?php
/**
 * @package     jelix
 * @subpackage scripts
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2005-2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 *
 * @deprecated
 */
if (defined('DECLARE_MYCOMMANDS')) {
    $application = \Jelix\DevHelper\JelixCommands::setup();
} else {
    \Jelix\DevHelper\JelixCommands::launch();
}
