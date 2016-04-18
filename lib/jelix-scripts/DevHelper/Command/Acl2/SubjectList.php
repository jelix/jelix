<?php
/**
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2007-2016 Laurent Jouanneau, 2008 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

namespace Jelix\DevHelper\Command\Acl2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;


class SubjectList  extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('acl2:subjects-list')
            ->setDescription('List of subjects')
            ->setHelp('')
        ;
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Subject Group', 'id', 'label key'));

        $cnx = \jDb::getConnection('jacl2_profile');
        $sql="SELECT id_aclsbj, s.label_key, s.id_aclsbjgrp, g.label_key as group_label_key FROM "
           .$cnx->prefixTable('jacl2_subject')." s
           LEFT JOIN ".$cnx->prefixTable('jacl2_subject_group')." g
           ON (s.id_aclsbjgrp = g.id_aclsbjgrp)
           ORDER BY s.id_aclsbjgrp, id_aclsbj";
        $rs = $cnx->query($sql);
        foreach($rs as $rec){
            $table->addRow(array(
                                $rec->id_aclsbjgrp,
                                $rec->id_aclsbj,
                                $rec->label_key
                                ));
        }
        $table->render();
    }
}
