<?php
/**
 * @author      Christophe Thiriot
 * @contributor Loic Mathaud
 * @contributor Laurent Jouanneau
 *
 * @copyright   2006 Christophe Thiriot, 2007-2018 Laurent Jouanneau
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
            ->setName('app:cleartemp')
            ->setDescription('Delete cache files')
            ->setHelp('')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $tempPath = \jApp::tempBasePath();
            if ($tempPath == DIRECTORY_SEPARATOR || $tempPath == '' || $tempPath == '/') {
                $output->writeln("<error>Error: bad path in jApp::tempBasePath(), it is equals to '".$tempPath."' !!</error>");
                $output->writeln('       Jelix cannot clear the content of the temp directory.');
                $output->writeln('       Correct the path in your application.init.php or create the corresponding directory');

                return 1;
            }
            if (!\jFile::removeDir($tempPath, false, array('.svn', '.git', '.dummy', '.empty'))) {
                $output->writeln('Some temp files were not removed');
            } elseif ($output->isVerbose()) {
                $output->writeln('All temp files have been removed');
            }
        } catch (\Exception $e) {
            $output->writeln("One or more directories couldn't be deleted.");
            $output->writeln('<error>Error: '.$e->getMessage().'</error>');

            return 2;
        }
        return 0;
    }
}
