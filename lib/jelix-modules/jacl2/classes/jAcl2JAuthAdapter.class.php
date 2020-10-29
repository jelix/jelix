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
 * Adapter to jAuth for jAcl2
 * @since 1.7.6
 */
class jAcl2JAuthAdapter implements jAcl2AuthAdapterInterface
{
    /**
     * @inheritDoc
     */
    public function getCurrentUserLogin()
    {
        if (jAuth::isConnected()) {
            return jAuth::getUserSession()->login;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isUserConnected()
    {
        return jAuth::isConnected();
    }
}