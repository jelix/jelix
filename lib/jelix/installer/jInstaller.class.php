<?php
/**
 * @package     jelix
 * @subpackage  installer
 *
 * @author      Laurent Jouanneau
 * @copyright   2008-2021 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once JELIX_LIB_PATH.'installer/jIInstallReporter.iface.php';

require_once JELIX_LIB_PATH.'installer/jInstallerReporterTrait.trait.php';

require_once JELIX_LIB_PATH.'installer/textInstallReporter.class.php';

require_once JELIX_LIB_PATH.'installer/ghostInstallReporter.class.php';

require_once JELIX_LIB_PATH.'installer/jInstallerException.class.php';

require_once JELIX_LIB_PATH.'installer/jInstallerModule.class.php';

require_once JELIX_LIB_PATH.'installer/jInstallerEntryPoint.class.php';

require_once JELIX_LIB_PATH.'core/jConfigCompiler.class.php';

require_once JELIX_LIB_PATH.'installer/jInstallerMessageProvider.class.php';

/**
 * main class for the installation.
 *
 * @deprecated
 * @see \Jelix\Installer\Installer
 */
class jInstaller extends \Jelix\Installer\Installer
{
}
