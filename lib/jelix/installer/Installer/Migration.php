<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016-2019 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;

/**
 * do changes in the application before the installation of modules can be done.
 *
 * It is used for directory changes etc.
 *
 * @since 1.7
 */
class Migration
{
    /**
     * the object responsible of the results output.
     *
     * @var Reporter\ReporterInterface
     */
    protected $reporter;

    public function __construct(Reporter\ReporterInterface $reporter)
    {
        $this->reporter = $reporter;
    }

    public function migrate()
    {
        $this->reporter->start();

        // Migration objects should be idempotent
        $jelix17 = new Migrator\Jelix17($this->reporter);
        $jelix17->migrate();

        $this->reporter->end();
    }

    public function migrateLocal()
    {
        $installFile = \jApp::varConfigPath('installer.ini.php');
        if (!file_exists($installFile)) {
            return;
        }
        $this->reporter->start();

        // Migration objects should be idempotent
        $jelix17 = new Migrator\Jelix17($this->reporter);
        $jelix17->localMigrate();

        $this->reporter->end();
    }
}
