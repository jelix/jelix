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

class UserRegister extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('acl2user:register')
            ->setDescription('Register a user and creates his private group')
            ->setHelp('')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'the user login'
            )
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $login = $input->getArgument('login');

        $cnx = \jDb::getConnection('jacl2_profile');

        $loginq = $cnx->quote($login);

        $sql = 'SELECT * FROM '.$cnx->prefixTable('jacl2_user_group')
            ." WHERE login = {$loginq}";
        $rs = $cnx->query($sql);
        if ($rec = $rs->fetch()) {
            throw new \Exception('the user is already registered');
        }

        $groupid = $cnx->quote('__priv_'.$login);

        $sql = 'INSERT into '.$cnx->prefixTable('jacl2_group')
            .' (id_aclgrp, name, grouptype, ownerlogin) VALUES (';
        $sql .= $groupid.','.$loginq.',2, '.$loginq.')';
        $cnx->exec($sql);

        $sql = 'INSERT INTO '.$cnx->prefixTable('jacl2_user_group')
            .' (login, id_aclgrp) VALUES('.$loginq.', '.$groupid.')';
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            $output->writeln("user {$login} is added into rights system and has a private group {$groupid}");
        }
    }
}
