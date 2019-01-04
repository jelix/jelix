<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

require(__DIR__.'/changeVersion.lib.php');

class BumpVersion  extends Command {

    protected function configure()
    {
        $this
            ->setName('version:bump')
            ->setDescription('Bump jelix version to a new version and tag/commit.')
            ->setHelp('')
            ->addArgument(
                'version',
                InputArgument::REQUIRED,
                'Version to commit'
            )
            ->addArgument(
                'nextVersion',
                InputArgument::OPTIONAL,
                'Version of the development version for the futur release'
            )
            ->addOption(
                'no-commit',
                '',
                InputOption::VALUE_NONE,
                ''
            )
            ->addOption(
                'path',
                '',
                InputOption::VALUE_REQUIRED,
                ''
            )
            ->addOption(
                'no-testapp',
                '',
                InputOption::VALUE_NONE,
                ''
            )
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $strVersion = $input->getArgument('version');
        $strNextVersion = $input->getArgument('nextVersion');
        $output->writeln("version $strVersion.");

        $path = $input->getOption('path');
        if (!$path) {
            $path = __DIR__.'/../';
        }

        $modifier = new ChangeVersion($path, $output);

        $modifier->changeVersionInJelix($strVersion);
        if (!$input->getOption('no-testapp')) {
            $modifier->changeVersionInTestapp($strVersion);
        }

        if (!$input->getOption('no-commit') && !$input->getOption('path')) {
            $this->commitChange("Release Jelix $strVersion", 'v'.$strVersion);
        }

        if ($strNextVersion) {

            $modifier->changeVersionInJelix($strNextVersion);
            if (!$input->getOption('no-testapp')) {
                $modifier->changeVersionInTestapp($strNextVersion);
            }

            if (!$input->getOption('no-commit') && !$input->getOption('path')) {
                $this->commitChange("Bumped version to  $strNextVersion");
            }
        }
    }

    protected function commitChange($message, $tag ='') {
        foreach (ChangeVersion::FILES as $file => $process) {
            passthru('git add "'.$file.'"');
        }
        foreach (ChangeVersion::TESTAPP_FILES as $file => $process) {
            passthru('git add "'.$file.'"');
        }
        passthru('git commit -m "'.$message.'"');
        if ($tag) {
            passthru('git tag '.$tag);
        }
    }
}

class SetNewVersionApplication extends Application
{
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'version:bump';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new BumpVersion();

        return $defaultCommands;
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}


$application = new SetNewVersionApplication();
$application->run();

