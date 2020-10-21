<?php
/**
 * @package     jelix_modules
 * @subpackage  jacl2
 *
 * @author      Laurent Jouanneau
 * @copyright   2020 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */

/**
 * Interface for authentication adapters for jAcl2
 * @since 1.7.6
 */
interface jAcl2AuthAdapterInterface
{

    /**
     * @return bool true if the current user is authenticated
     */
    public function isUserConnected();

    /**
     * @return string|null the login or null if the user is not connected
     */
    public function getCurrentUserLogin();
}