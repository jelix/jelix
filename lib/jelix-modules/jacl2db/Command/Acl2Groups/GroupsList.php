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

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupsList extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('acl2group:list')
            ->setDescription('Shows list of users groups')
            ->setHelp('')
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Id', 'label', 'default'));

        $cnx = \jDb::getConnection('jacl2_profile');
        $sql = 'SELECT id_aclgrp, name, grouptype FROM '
            .$cnx->prefixTable('jacl2_group')
            .' WHERE grouptype <2 ORDER BY name';
        $rs = $cnx->query($sql);

        foreach ($rs as $rec) {
            if ($rec->grouptype == 1) {
                $type = 'yes';
            } else {
                $type = '';
            }

            $table->addRow(array(
                $rec->id_aclgrp,
                $rec->name,
                $type,
            ));
        }
        $table->render();
    }
}
