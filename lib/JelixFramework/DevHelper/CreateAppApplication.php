<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016-2025 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\DevHelper;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

class CreateAppApplication extends Application
{
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input): ?string
    {
        return 'app:create';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands(): array
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = $this->createAppCmd;

        return $defaultCommands;
    }

    protected $createAppCmd;

    public function initCreateAppCommand($jelixPath, $jelixAsComposerPackage, $vendorPath, $defaultRule, $forbiddenRule = '')
    {
        $this->createAppCmd = new \Jelix\DevHelper\Command\CreateApp(
            $jelixPath,
            $jelixAsComposerPackage,
            $vendorPath,
            $defaultRule,
            $forbiddenRule
        );
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition(): \Symfony\Component\Console\Input\InputDefinition
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
