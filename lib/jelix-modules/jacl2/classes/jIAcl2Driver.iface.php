<?php
/**
 * @package     jacl2
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2008 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 *
 * @since 1.1
 */

/**
 * interface for jAcl2 drivers.
 *
 * @package jelix
 * @subpackage acl
 */
interface jIAcl2Driver
{
    /**
     * Return the possible values of the right on the given role (and on the optional resource).
     *
     * @param string $role  the key of the role
     * @param string $resource the id of a resource
     *
     * @return array list of values corresponding to the right
     */
    public function getRight($role, $resource = null);

    /**
     * Clear some cached data, it a cache exists in the driver..
     */
    public function clearCache();
}
