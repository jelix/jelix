<?php
/**
 * @package     jelix
 *
 * @author      Laurent Jouanneau
 * @copyright   2025 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Migrator;

use Jelix\Core\Config\AppConfig;

class Jelix19
{
    /**
     * the object responsible of the results output.
     *
     * @var \Jelix\Installer\Reporter\ReporterInterface
     */
    protected $reporter;

    /**
     * @var \Jelix\IniFile\IniReader
     */
    protected $defaultConfigIni;

    public function __construct(\Jelix\Installer\Reporter\ReporterInterface $reporter)
    {
        $this->reporter = $reporter;
        $this->defaultConfigIni = new \Jelix\IniFile\IniReader(AppConfig::getDefaultConfigFile());
    }

    public function migrate()
    {
        $this->reporter->message('Start migration to Jelix 1.9.0', 'notice');

        $this->reporter->message('Migration to Jelix 1.9.0 is done', 'notice');
    }

    public function localMigrate()
    {
        \Jelix\FileUtilities\Directory::create(\jApp::varLibPath());
        file_put_contents(\jApp::varLibPath('.dummy'), '');

        $this->reporter->message('Migration of local configuration to Jelix 1.7.0 is done', 'notice');
    }


    protected function error($msg)
    {
        $this->reporter->message($msg, 'error');
    }

    protected function ok($msg)
    {
        $this->reporter->message($msg, '');
    }

    protected function warning($msg)
    {
        $this->reporter->message($msg, 'warning');
    }

    protected function notice($msg)
    {
        $this->reporter->message($msg, 'notice');
    }
}
