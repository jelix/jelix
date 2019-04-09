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

class GroupCreate extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('acl2group:create')
            ->setDescription('Create a new user group')
            ->setHelp('')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'the group id to create'
            )
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'the name of the group'
            )
            ->addOption(
                'default',
                null,
                InputOption::VALUE_NONE,
                'To set the new group as default group for new users'
            )
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = $input->getArgument('group');
        $name = $input->getArgument('name');
        $isDefault = $input->getOption('default');

        if (!$name) {
            $name = $group;
        }
        $cnx = \jDb::getConnection('jacl2_profile');

        try {
            $sql = 'INSERT into '.$cnx->prefixTable('jacl2_group')
                .' (id_aclgrp, name, grouptype, ownerlogin) VALUES (';
            $sql .= $cnx->quote($group).',';
            $sql .= $cnx->quote($name).',';
            if ($isDefault) {
                $sql .= '1, NULL)';
            } else {
                $sql .= '0, NULL)';
            }
            $cnx->exec($sql);
        } catch (\Exception $e) {
            throw new \Exception('this group already exists');
        }

        if ($output->isVerbose()) {
            $output->writeln("Group '".$group."' is created");
        }
    }
}
