<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2007-2016 Laurent Jouanneau, 2008 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\Acl2Db\Command\Acl2;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubjectList extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('acl2:rights-list')
            ->setDescription('List of rights')
            ->setHelp('')
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Rights Group', 'Right id', 'label key'));

        $cnx = \jDb::getConnection('jacl2_profile');
        $sql = 'SELECT id_aclsbj, s.label_key, s.id_aclsbjgrp, g.label_key as group_label_key FROM '
           .$cnx->prefixTable('jacl2_subject').' s
           LEFT JOIN '.$cnx->prefixTable('jacl2_subject_group').' g
           ON (s.id_aclsbjgrp = g.id_aclsbjgrp)
           ORDER BY s.id_aclsbjgrp, id_aclsbj';
        $rs = $cnx->query($sql);
        foreach ($rs as $rec) {
            $table->addRow(array(
                $rec->id_aclsbjgrp,
                $rec->id_aclsbj,
                $rec->label_key,
            ));
        }
        $table->render();
    }
}
