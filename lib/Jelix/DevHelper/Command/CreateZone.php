<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2005-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateZone extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('module:create-zone')
            ->setDescription('Create a new zone')
            ->setHelp('')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the module where to create the zone'
            )
            ->addArgument(
                'zone',
                InputArgument::REQUIRED,
                'the name of the zone'
            )
            ->addArgument(
                'template',
                InputArgument::OPTIONAL,
                'name of the template created with the zone (by default, the template name is the zone name)'
            )
            ->addOption(
                'no-tpl',
                null,
                InputOption::VALUE_NONE,
                'no template is created.'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $name = $input->getArgument('zone');
        $template = $input->getArgument('template');

        $path = $this->getModulePath($module);

        $dirname = $path.'zones/';
        $this->createDir($dirname);

        $filename = strtolower($name).'.zone.php';

        $param = array('name' => $name,
            'module' => $module, );

        if (!$input->getOption('no-tpl')) {
            if ($template) {
                $param['template'] = $template;
            } else {
                $param['template'] = strtolower($name);
            }
            $this->createFile($path.'templates/'.$param['template'].'.tpl', 'module/template.tpl', $param, 'Template');
        } else {
            $param['template'] = '';
        }
        $this->createFile($dirname.$filename, 'module/zone.tpl', $param, 'Zone');
        return 0;
    }
}
