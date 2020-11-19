<?php
/**
 * @package    jelix
 * @subpackage installer
 *
 * @author     Laurent Jouanneau
 * @copyright  2011 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * Application configuration reader and manager.
 *
 * @package    jelix
 * @subpackage installer
 *
 * @since 1.3
 * @deprecated
 */
class jInstallerApplication
{
    /**
     * @var string the application name
     */
    protected $appName = '';

    /**
     * the global app setup.
     *
     * @var \Jelix\Installer\GlobalSetup
     */
    protected $globalSetup;

    /**
     * @param string $projectFile the filename of the XML project file
     */
    public function __construct($projectFile = '', Jelix\Installer\GlobalSetup $globalSetup = null)
    {
        if (!$globalSetup) {
            $globalSetup = new \Jelix\Installer\GlobalSetup();
        }
        $this->globalSetup = $globalSetup;
    }

    public function getEntryPointsList()
    {
        return $this->globalSetup->getEntryPointsList();
    }

    public function getEntryPointInfo($name)
    {
        if (($p = strpos($name, '.php')) !== false) {
            $name = substr($name, 0, $p);
        }

        return $this->globalSetup->getEntryPointById($name);
    }
}
