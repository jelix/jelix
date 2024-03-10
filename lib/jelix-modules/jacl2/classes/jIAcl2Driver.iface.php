<?php
/**
 * @package     jelix
 * @subpackage  jacl2
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
 * @subpackage jacl2
 */
interface jIAcl2Driver
{
    /**
     * Says if there is a right on the given right (and on the optional resource)
     * for the current user.
     *
     * @param string $right    the key of the right
     * @param string $resource the id of a resource
     *
     * @return bool true if the right exists
     */
    public function getRight($right, $resource = null);

    /**
     * Clear some cached data, it a cache exists in the driver..
     */
    public function clearCache();
}
