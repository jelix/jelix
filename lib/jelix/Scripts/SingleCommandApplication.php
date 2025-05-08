<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\Scripts;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SingleCommandApplication extends Application
{

    public function __construct(Command $command, $name = 'UNKNOWN')
    {
        parent::__construct($name);
        $this->add($command);
        $this->setDefaultCommand($command->getName(), true);
    }

    public function getDefinition(): \Symfony\Component\Console\Input\InputDefinition
    {
        $inputDefinition = parent::getDefinition();

        // activate the xdebug option
        if (function_exists('xdebug_connect_to_client')) {
            $inputDefinition->addOption(
                new InputOption(
                    'xdebug',
                    '',
                    InputOption::VALUE_NONE,
                    'activate Xdebug to debug this command'
                )
            );
        }
        return $inputDefinition;
    }

    /**
     * Runs the current application.
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (function_exists('xdebug_connect_to_client') && true === $input->hasParameterOption('--xdebug', true)) {
            \xdebug_connect_to_client();
        }
        return parent::doRun($input, $output);
    }
}
