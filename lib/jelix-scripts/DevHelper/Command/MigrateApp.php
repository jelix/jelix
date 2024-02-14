<?php

/**
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2008-2018 Laurent Jouanneau
 * @copyright   2009 Julien Issler
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateApp extends \Jelix\DevHelper\AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('jelix:migrate')
            ->setDescription('Migrate files of an old Jelix application to the current Jelix version')
            ->setHelp('')
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = parent::execute($input, $output);
        if ($code) {
            return $code;
        }

        require_once JELIX_LIB_PATH.'installer/jInstaller.class.php';

        if ($this->verbose()) {
            $reporter = new \Jelix\Installer\Reporter\Console($output, 'notice', 'Low-level migration');
        } else {
            $reporter = new \Jelix\Installer\Reporter\Console($output, 'error', 'Low-level migration');
        }

        // launch the low-level migration
        $migrator = new \Jelix\Installer\Migration($reporter);
        $migrator->migrate();
        return 0;
    }
}
