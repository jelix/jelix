<?php
/**
 * @package     jelix
 * @subpackage  jacl2
 *
 * @author      Laurent Jouanneau
 * @copyright   2020-2023 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */

/**
 * Adapter to jAuth for jAcl2.
 *
 * @since 1.7.6
 */
class jAcl2JAuthAdapter implements jAcl2AuthAdapterInterface2
{
    /**
     * {@inheritdoc}
     */
    public function getCurrentUserLogin()
    {
        if (jAuth::isConnected()) {
            return jAuth::getUserSession()->login;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isUserConnected()
    {
        return jAuth::isConnected();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfilUrl($login)
    {
        if (\jApp::isModuleEnabled('jcommunity') && \jAcl2::check('auth.users.view')) {
            return jUrl::get('jcommunity~account:show', array('user'=> $login));
        }
        if (\jApp::isModuleEnabled('jauthdb_admin') && \jAcl2::check('auth.user.view')) {
            return jUrl::get('jauthdb_admin~user:index', array('j_user_login'=> $login));
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserAdminProfilUrl($login)
    {
        if (\jApp::isModuleEnabled('jauthdb_admin') && \jAcl2::check('auth.users.view')) {
            return jUrl::get('jauthdb_admin~default:view', array('j_user_login'=> $login));
        }

        return '';
    }
}
