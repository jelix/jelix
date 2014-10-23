<?php
/**
* @author   Laurent Jouanneau
* @copyright 2014 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Checker;

/**
 * Allow to access to some localized messages
 */
class Messages extends \Jelix\SimpleLocalization\Container {

    function __construct($lang = '' ) {
        parent::__construct(__DIR__.'/installmessages.%LANG%.php', $lang);
    }
}
