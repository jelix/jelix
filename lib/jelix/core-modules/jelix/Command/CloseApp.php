<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2010-2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\JelixModule\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloseApp extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('app:close')
            ->setDescription('Close the application. It will not accessible anymore from the web.')
            ->setHelp('')
            ->addArgument(
                'message',
                InputArgument::OPTIONAL,
                'A message for user, indicating the reason.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \jAppManager::close($input->getArgument('message'));
        if ($output->isVerbose()) {
            $output->writeln('Application is closed.');
        }
    }
}
