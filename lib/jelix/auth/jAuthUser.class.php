<?php
/**
* @package    jelix
* @subpackage auth
* @author     Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright  2006 Laurent Jouanneau
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * default object to represent a user
 *
 * this is only a data container. In fact, auth drivers can provide
 * other object to embed user data.
 * @package    jelix
 * @subpackage auth
 */
class jAuthUser {
    public $login = '';
    public $email ='';
}

?>