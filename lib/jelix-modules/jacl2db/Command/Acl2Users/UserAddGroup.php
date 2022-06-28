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

class UserAddGroup extends \Jelix\Acl2Db\Command\Acl2\AbstractAcl2Cmd
{
    protected function configure()
    {
        $this
            ->setName('acl2user:addgroup')
            ->setDescription('Add a user into a group')
            ->setHelp('')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'the login of the user'
            )
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'the group id in which the user should be added'
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

        $sql = 'SELECT * FROM '.$cnx->prefixTable('jacl2_user_group')
            .' WHERE login= '.$cnx->quote($login).' AND id_aclgrp = '.$cnx->quote($groupid);
        $rs = $cnx->query($sql);
        if ($rec = $rs->fetch()) {
            throw new \Exception('The user is already in this group');
        }

        $sql = 'SELECT * FROM  '.$cnx->prefixTable('jacl2_user_group').' u, '
                .$cnx->prefixTable('jacl2_group').' g
                WHERE u.id_aclgrp = g.id_aclgrp AND login= '.$cnx->quote($login).' AND grouptype = 2';
        $rs = $cnx->query($sql);
        if (!($rec = $rs->fetch())) {
            throw new \Exception("The user doesn't exist");
        }

        $sql = 'INSERT INTO '.$cnx->prefixTable('jacl2_user_group')
            .' (login, id_aclgrp) VALUES('.$cnx->quote($login).', '.$cnx->quote($groupid).')';
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            $output->writeln("User '".$login."' is added into group '".$group."'");
        }
        return 0;
    }
}
