<?php

namespace Jelix\Scripts;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class SingleCommandApplication extends Application
{
    protected $myCommand;

    public function __construct(Command $command, $name = 'UNKNOWN')
    {
        parent::__construct($name);
        $this->myCommand = $command;
    }

    protected function getCommandName(InputInterface $input)
    {
        return $this->myCommand->getName();
    }

    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = $this->myCommand;

        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
