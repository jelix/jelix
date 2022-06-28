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

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UsersList extends \Jelix\Acl2Db\Command\Acl2\AbstractAcl2Cmd
{
    protected function configure()
    {
        $this
            ->setName('acl2user:list')
            ->setDescription('List of users')
            ->setHelp('')
            ->addArgument(
                'group',
                InputArgument::OPTIONAL,
                'the group id filter'
            )
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cnx = \jDb::getConnection('jacl2_profile');
        $table = new Table($output);
        $groupFiler = false;

        if ($input->getArgument('group')) {
            $id = $this->_getGrpId($input, true);
            $sql = 'SELECT login FROM '.$cnx->prefixTable('jacl2_user_group')
                    .' WHERE id_aclgrp ='.$cnx->quote($id);
            $table->setHeaders(array('Login'));
            $groupFiler = true;
        } else {
            $sql = 'SELECT login, g.id_aclgrp, name FROM '
                .$cnx->prefixTable('jacl2_user_group').' AS u '
                .' LEFT JOIN '.$cnx->prefixTable('jacl2_group').' AS g
                ON (u.id_aclgrp = g.id_aclgrp AND g.grouptype < 2)
                ORDER BY login';
            $table->setHeaders(array('Login', 'group', 'group id'));
        }

        $cnx = \jDb::getConnection('jacl2_profile');
        $rs = $cnx->query($sql);
        foreach ($rs as $rec) {
            if ($groupFiler) {
                $table->addRow(array(
                    $rec->login,
                ));
            } else {
                $table->addRow(array(
                    $rec->login,
                    $rec->name,
                    $rec->id_aclgrp,
                ));
            }
        }
        $table->render();
        return 0;
    }
}
