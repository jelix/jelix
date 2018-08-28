<?php
/**
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2007-2016 Laurent Jouanneau, 2008 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

namespace Jelix\Acl2Db\Command\Acl2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class SubjectGroupList  extends \Jelix\Scripts\ModuleCommandAbstract {

    protected function configure()
    {
        $this
            ->setName('acl2:sg-list')
            ->setDescription('List of subject groups')
            ->setHelp('')
        ;
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('id', 'label key'));

        $cnx = \jDb::getConnection('jacl2_profile');
        $sql="SELECT id_aclsbjgrp, label_key FROM "
           .$cnx->prefixTable('jacl2_subject_group')." ORDER BY id_aclsbjgrp";
        $rs = $cnx->query($sql);
        foreach($rs as $rec){
            $table->addRow(array(
                                $rec->id_aclsbjgrp,
                                $rec->label_key
                                ));
        }
        $table->render();

    }
}
