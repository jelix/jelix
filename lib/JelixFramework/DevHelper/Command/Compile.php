<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2024-2026 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence    GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Jelix\Core\Infos\FrameworkInfos;
use Jelix\Installer\WarmUp\WarmUp;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Compile extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('compile')
            ->setDescription('launch the compiler corresponding to the given file, if it exists')
            ->setHelp('You can use this script for file watchers, into your IDE')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The full path to the file to compile'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $warmUp = new WarmUp(\jApp::app(), FrameworkInfos::load());
        $warmUp->launchForFile($input->getArgument('file'));
        return 0;
    }
}
