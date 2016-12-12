<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2008-2016 Laurent Jouanneau
* @copyright   2009 Julien Issler
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallApp extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('app:install')
            ->setDescription('Execute install/update scripts from all activated modules')
            ->setHelp('')
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        \Jelix\Core\AppManager::close();

        if ($this->verbose()) {
            $reporter = new \Jelix\Installer\Reporter\Console('notice', 'Low-level migration');
        }
        else {
            $reporter = new \Jelix\Installer\Reporter\Console('error', 'Low-level migration');
        }

        // launch the low-level migration
        $migrator = new \Jelix\Installer\Migration($reporter);
        $migrator->migrate();

        // we can now launch the installer/updater
        if ($this->verbose()) {
            $reporter = new \Jelix\Installer\Reporter\Console();
        }
        else {
            $reporter = new \Jelix\Installer\Reporter\Console('error');
        }

        $installer = new \Jelix\Installer\Installer($reporter);

        if ($input->getOption('entry-point')) {
            $installer->installEntryPoint($this->entryPointId);
        }
        else {
            $installer->installApplication();
        }

        try {
            \Jelix\Core\AppManager::clearTemp(\Jelix\Core\App::tempBasePath());
        }
        catch(\Exception $e) {
            if ($e->getCode() == 2) {
                $output->writeln("<error>Error: bad path in jApp::tempBasePath(), it is equals to '".\jApp::tempBasePath()."' !!</error>");
                $output->writeln("       Jelix cannot clear the content of the temp directory.");
                $output->writeln("       you must clear it your self.");
                $output->writeln("       Correct the path in the application.init.php or create the directory");
            }
            else {
                $output->writeln("<error>Error: ".$e->getMessage()."</error>");
            }
        }
        \Jelix\Core\AppManager::open();
    }
}
