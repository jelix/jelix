<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */
namespace Testapp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HelloWorldCommand  extends \Jelix\Scripts\ModuleCommandAbstract {

    protected function configure()
    {
        $this
            ->setName('testapp:hello')
            ->setDescription('Say Hello')
            ->setHelp('')
            ->addArgument(
                'firstname',
                InputArgument::OPTIONAL,
                'A name.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('firstname');
        if (!$name) {
            $name = 'World';
        }
        $output->writeln("Hello $name.");

        $url = \jUrl::getFull('jelix_tests~jstests:jforms');
        $output->writeln("Url: $url.");

        return 0;
    }
}