<?php
/**
 * @package    jelix
 * @subpackage auth
 *
 * @author     Tahina Ramaroson
 * @contributor Sylvain de Vathaire, Laurent Jouanneau
 *
 * @copyright  2009 Neov, 2023 Laurent Jouanneau
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
require_once JELIX_LIB_PATH.'auth/jAuthUser.class.php';

/**
 * object which represent a user for LDAP driver.
 *
 * @package    jelix
 * @subpackage auth
 */
#[AllowDynamicProperties]
class jAuthUserLDAP extends jAuthUser
{
    public $password;
}
