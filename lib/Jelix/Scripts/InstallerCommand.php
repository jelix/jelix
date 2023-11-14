<?php
/**
 * @author Laurent Jouanneau
 * @copyright   2018-2023 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\Scripts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('installer')
            ->setDescription('Launch installers of the application')
            ->setHelp('')
            ->addOption(
                'no-clean-temp',
                '',
                InputOption::VALUE_NONE,
                'Do not delete files from temp directory.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Jelix\Core\AppManager::close();

        if ($output->isQuiet()) {
            $reporter = new \Jelix\Installer\Reporter\Console($output, 'error', 'Installation');
        } else {
            $reporter = new \Jelix\Installer\Reporter\Console($output, 'notice', 'Installation');
        }

        $installer = new \Jelix\Installer\Installer($reporter);
        if (!$installer->installApplication()) {
            return 1;
        }

        if ($input->getOption('no-clean-temp')) {
            \jAppManager::open();
            return 0;
        }

        try {
            \Jelix\Core\AppManager::clearTemp();
        } catch (\Exception $e) {
            $output->writeln('<error>Content of the temp directory cannot be removed because of this error:</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
            $code = $e->getCode();
            $output->writeln('<comment>You MUST delete files into '.\jApp::tempBasePath().'</comment>');
            if ($code == 4 || $code == 5) {
                $output->writeln('Fix rights on directories, then run:');
                $output->writeln('   php console.php app:cleartemp');
                $output->writeln('Or probably you cloud use sudo directly:');
                $output->writeln('   sudo php console.php app:cleartemp');
            }
            else {
                $output->writeln('Fix the error if needed, then run (possibly with sudo):');
                $output->writeln('   php console.php app:cleartemp');
            }
            $output->writeln('Or delete files by hand.');
        }
        \Jelix\Core\AppManager::open();

        return 0;
    }
}
