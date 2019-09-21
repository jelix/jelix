<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author     Laurent Jouanneau
 * @copyright  2006 Laurent Jouanneau
 * @licence    http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

/**
 * @package    jelix-modules
 * @subpackage jelix-module
 */
class errorCtrl extends jController
{
    /**
     * 404 error page.
     */
    public function notfound()
    {
        throw new jHttp404NotFoundException();
    }

    /**
     * 403 error page.
     *
     * @since 1.0.1
     */
    public function badright()
    {
        throw new jHttp403ForbiddenException();
    }
}
