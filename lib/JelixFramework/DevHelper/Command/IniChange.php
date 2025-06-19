<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2019-2023 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Jelix\IniFile\IniModifier;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IniChange extends \Jelix\DevHelper\AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('app:ini-change')
            ->setDescription('Modify an ini file')
            ->setHelp('')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The path to the ini file'
            )
            ->addArgument(
                'param',
                InputArgument::REQUIRED,
                'the parameter name into the ini file to change, or the section name to delete (--del-section option)'
            )
            ->addArgument(
                'value',
                InputArgument::OPTIONAL,
                'the value of the parameter',
                ''
            )
            ->addArgument(
                'section',
                InputArgument::OPTIONAL,
                'the section name if the parameter is into a section',
                ''
            )
            ->addOption(
                'del',
                null,
                InputOption::VALUE_NONE,
                'delete the parameter instead of setting it'
            )
            ->addOption(
                'del-section',
                null,
                InputOption::VALUE_NONE,
                'delete the section given as parameter name'
            )
            ->addOption(
                'create-file',
                null,
                InputOption::VALUE_NONE,
                'create the file if it doesn\'t exists'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $todel = $input->getOption('del');
        $createFile = $input->getOption('create-file');

        $file = $input->getArgument('file');
        $param = $input->getArgument('param');
        $value = $input->getArgument('value');
        $section = $input->getArgument('section');

        if ($section === null) {
            $section = 0;
        }

        if ($createFile && !file_exists($file)) {
            file_put_contents($file, '');
        }

        $ini = new IniModifier($file);

        if ($input->getOption('del-section')) {
            $ini->removeSection($param);
        } elseif ($todel) {
            $ini->removeValue($param, $section);
        } else {
            if ($value === null) {
                throw new \Exception('value is missing');
            }
            $ini->setValue($param, $value, $section);
        }
        $ini->save();
        return 0;
    }
}
