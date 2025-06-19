<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016-2023 Laurent Jouanneau
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
     * the object responsible on the results output.
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

        $jelix20 = new Migrator\Jelix20($this->reporter);
        $jelix20->migrate();

        $this->reporter->end();
    }

    public function migrateLocal()
    {
        $installFile = \Jelix\Core\App::varConfigPath('installer.ini.php');
        if (!file_exists($installFile)) {
            return;
        }
        $this->reporter->start();

        // Migration objects should be idempotent
        $jelix17 = new Migrator\Jelix17($this->reporter);
        $jelix17->localMigrate();

        $jelix20 = new Migrator\Jelix20($this->reporter);
        $jelix20->localMigrate();

        $this->reporter->end();
    }
}
