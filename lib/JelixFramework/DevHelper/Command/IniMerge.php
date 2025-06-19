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

class IniMerge extends \Jelix\DevHelper\AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('app:ini-merge')
            ->setDescription('Merge two ini file')
            ->setHelp('')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'The path to the ini file that will be merged into the target file'
            )
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'The path to the target ini file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $source = $input->getArgument('source');
        $target = $input->getArgument('target');

        if (!file_exists($source)) {
            throw new \Exception('Error: '.$source.' does not exists');
        }
        if (!file_exists($target)) {
            throw new \Exception('Error: '.$target.' does not exists');
        }

        $iniSource = new IniModifier($source);
        $iniTarget = new IniModifier($target);
        $iniTarget->import($iniSource);
        $iniTarget->save();
        return 0;
    }
}
