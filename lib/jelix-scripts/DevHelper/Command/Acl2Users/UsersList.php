<?php
/**
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @contributor Loic Mathaud
* @copyright   2007-2016 Laurent Jouanneau
* @copyright   2008 Julien Issler
* @copyright   2008 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

namespace Jelix\DevHelper\Command\Acl2Users;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;


class UsersList  extends \Jelix\DevHelper\Command\Acl2\AbstractAcl2Cmd {

    protected function configure()
    {
        $this
            ->setName('acl2user:list')
            ->setDescription('List of users')
            ->setHelp('')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'the group id filter'
            )
        ;
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $groupFiler = false;

        if ($input->getArgument('group')) {
            $id = $this->_getGrpId($input, true);
            $sql = "SELECT login FROM ".$cnx->prefixTable('jacl2_user_group')
                    ." WHERE id_aclgrp =".$id;
            $table->setHeaders(array('Login'));
            $groupFiler = true;
        }
        else {
            $sql="SELECT login, u.id_aclgrp, name FROM "
                .$cnx->prefixTable('jacl2_user_group')." u, "
                .$cnx->prefixTable('jacl2_group')." g
                WHERE g.grouptype <2 AND u.id_aclgrp = g.id_aclgrp ORDER BY login";
            $table->setHeaders(array('Login', 'group', 'group id'));
        }

        $cnx = \jDb::getConnection('jacl2_profile');
        $rs = $cnx->query($sql);
        foreach($rs as $rec){
            if ($groupFiler) {
                $table->addRow(array(
                                $rec->login
                                ));
            }
            else {
                $table->addRow(array(
                                $rec->login,
                                $rec->name,
                                $rec->id_aclgrp
                                ));
            }
        }
        $table->render();
    }
}
