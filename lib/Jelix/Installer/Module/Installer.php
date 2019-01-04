<?php
/**
* @author      Laurent Jouanneau
* @copyright   2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Module;

use Jelix\Installer\Module\API\InstallHelpers;
use Jelix\Installer\Module\API\PreInstallHelpers;

/**
 * Bas class for classes that does processing to install a module into
 * an instance of the application. A module should have a class that inherits
 * from it in order to setup itself into the application.
 *
 * @since 1.7
 */
class Installer extends InstallerAbstract implements InstallerInterface {

    /**
     * @inheritdoc
     */
    function preInstall(PreInstallHelpers $helpers) {

    }

    /**
     * @inheritdoc
     */
    function install(InstallHelpers $helpers) {

    }

    /**
     * @inheritdoc
     */
    function postInstall(InstallHelpers $helpers) {

    }

    /**
     * the versions for which the installer should be called.
     *
     * Useful for an upgrade which target multiple branches of a project.
     * Put the version for multiple branches. The installer will be called
     * only once, for the needed version.
     * If you don't fill it, the name of the class file should contain the
     * target version (deprecated behavior though)
     *
     * @var array $targetVersions list of version by asc order
     */
    protected $targetVersions = array();

    /**
     * @var string the date of the release of the update. format: yyyy-mm-dd hh:ii
     */
    protected $date = '';

    /**
     * @var string the version for which the installer is called
     */
    protected $version = '0';


    function getTargetVersions() {
        return $this->targetVersions;
    }

    function setTargetVersions($versions) {
        $this->targetVersions = $versions;
    }

    function getDate() {
        return $this->date;
    }

    function getVersion() {
        return $this->version;
    }

    function setVersion($version) {
        $this->version = $version;
    }

}

