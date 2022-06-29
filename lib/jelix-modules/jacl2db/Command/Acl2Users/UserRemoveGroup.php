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

namespace Jelix\Acl2Db\Command\Acl2Users;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserRemoveGroup extends \Jelix\Acl2Db\Command\Acl2\AbstractAcl2Cmd
{
    protected function configure()
    {
        $this
            ->setName('acl2user:removegroup')
            ->setDescription('Remove a user from a group')
            ->setHelp('')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'the login of the user'
            )
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'the group id from which the user should be removed'
            )
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = $input->getArgument('group');
        $login = $input->getArgument('login');

        $cnx = \jDb::getConnection('jacl2_profile');
        $groupid = $this->_getGrpId($input);

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_user_group')
            .' WHERE login='.$cnx->quote($login).' AND id_aclgrp='.$cnx->quote($groupid);
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            $output->writeln("User '".$login."' is removed from group '".$group."'");
        }
        return 0;
    }
}
