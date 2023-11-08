<?php
/**
 * @package     jelix_modules
 * @subpackage  jacl2
 *
 * @author      Laurent Jouanneau
 * @copyright   2023 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */

/**
 * Interface for authentication adapters for jAcl2.
 *
 * @since 1.8.4
 */
interface jAcl2AuthAdapterInterface2 extends jAcl2AuthAdapterInterface
{

    /**
     * @param string $login
     * @return string the url to the public profile of the given user. If empty,
     *                there is no a page to show the profile.
     */
    public function getUserProfilUrl($login);

    /**
     * @param string $login
     * @return string the url to the profile of the given user from the user administration.
     *                  If empty, there is no a page to show the profile.
     */
    public function getUserAdminProfilUrl($login);

}
