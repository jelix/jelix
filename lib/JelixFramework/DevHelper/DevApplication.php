<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2024-2025 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\DevHelper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;

class DevApplication extends Application
{
    public function getDefinition(): InputDefinition
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