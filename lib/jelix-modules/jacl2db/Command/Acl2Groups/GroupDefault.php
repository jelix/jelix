<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 * @contributor Loic Mathaud
 *
 * @copyright   2007-2016 Laurent Jouanneau
 * @copyright   2008 Julien Issler
 * @copyright   2008 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\Acl2Db\Command\Acl2Groups;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GroupDefault extends \Jelix\Acl2Db\Command\Acl2\AbstractAcl2Cmd
{
    protected function configure()
    {
        $this
            ->setName('acl2group:default')
            ->setDescription('Set a user group as default group for new users')
            ->setHelp('')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'the group id'
            )
            ->addOption(
                'no-default',
                null,
                InputOption::VALUE_NONE,
                'To set the group as non default group for new users'
            )
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = $input->getArgument('group');
        $def = ($input->getOption('no-default') ? 0 : 1);
        $id = $this->_getGrpId($input, true);

        $cnx = \jDb::getConnection('jacl2_profile');

        $sql = 'UPDATE '.$cnx->prefixTable('jacl2_group')
            ." SET grouptype={$def}  WHERE id_aclgrp=".$cnx->quote($id);
        $cnx->exec($sql);
        if ($output->isVerbose()) {
            $output->writeln("Group '".$group."' is ".($def ? ' now a default group' : ' no more a default group'));
        }
    }
}
