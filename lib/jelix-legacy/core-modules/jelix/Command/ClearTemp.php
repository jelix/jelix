<?php
/**
 * @author      Christophe Thiriot
 * @contributor Loic Mathaud
 * @contributor Laurent Jouanneau
 *
 * @copyright   2006 Christophe Thiriot, 2007-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\JelixModule\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearTemp extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('app:clean-temp')
            ->setAliases(['app:cleartemp', 'app:clear-temp'])
            ->setDescription('Delete temporary files')
            ->setHelp('')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            \Jelix\Core\AppManager::clearTemp();
            if ($output->isVerbose()) {
                $output->writeln('All temp files have been removed');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Content of the temp directory cannot be removed because of this error:</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
            $code = $e->getCode();
            if ($code == 4 || $code == 5) {
                $output->writeln('Fix rights on directories, then relaunch the command');
                $output->writeln('Or probably you cloud use sudo directly:');
                $output->writeln('   sudo php console.php app:cleartemp');
            }
            else {
                $output->writeln('Fix the error if needed, then re-run the command, possibly with sudo.');
            }
            $output->writeln('Or delete files by hand.');
            return $e->getCode();
        }
        return 0;
    }
}
