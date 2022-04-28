<?php

/**
 * @package     jelix-scripts
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 * @contributor Bastien Jaillot
 *
 * @copyright   2005-2016 Laurent Jouanneau, 2008 Bastien Jaillot
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCtrl extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('module:create-ctrl')
            ->setDescription('Create a new controller')
            ->setHelp('')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'module name where to create the controller'
            )
            ->addArgument(
                'controller',
                InputArgument::REQUIRED,
                'name of your new controller'
            )
            ->addArgument(
                'method',
                InputArgument::OPTIONAL,
                'name of the first method (\'index\' by default)',
                'index'
            )
            ->addOption(
                'add-install-zone',
                null,
                InputOption::VALUE_NONE,
                'Add the check_install zone for new application.'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $controller = $input->getArgument('controller');

        $path = $this->getModulePath($module);

        $agfilename = $path.'controllers/';
        $this->createDir($agfilename);

        $type = 'classic';
        $ctrlname = strtolower($controller).'.'.$type.'.php';
        $agfilename .= $ctrlname;

        $method = $input->getArgument('method');

        $param = array('name' => $controller,
            'method' => $method,
            'module' => $module, );

        if ($input->getOption('add-install-zone')) {
            $tplname = 'module/controller.newapp.tpl';
        } else {
            $tplname = 'module/controller.tpl';
        }
        $this->createFile($agfilename, $tplname, $param, 'Controller');
        return 0;
    }
}
