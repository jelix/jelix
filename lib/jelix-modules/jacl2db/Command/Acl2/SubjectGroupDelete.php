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
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SubjectGroupDelete  extends \Jelix\Scripts\ModuleCommandAbstract {

    protected function configure()
    {
        $this
            ->setName('acl2:sg-delete')
            ->setDescription('Delete a subject group')
            ->setHelp('')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'Name of the subject group'
            )
            ->addOption(
               'confirm',
               null,
               InputOption::VALUE_NONE,
               'Avoid to wait after user confirmation'
            )
        ;
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = $input->getArgument('group');

        if (!$input->getOption('confirm')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('are you sure you want to delete subject group '.$group.' (y/N)?', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('command canceled');
                return;
            }
        }

        $cnx = \jDb::getConnection('jacl2_profile');

        $sql="SELECT id_aclsbjgrp FROM ".$cnx->prefixTable('jacl2_subject_group')
            ." WHERE id_aclsbjgrp=".$cnx->quote($group);
        $rs = $cnx->query($sql);
        if (!$rs->fetch()) {
            throw new \Exception("This subject group does not exist");
        }

        $sql="UDPATE ".$cnx->prefixTable('jacl2_rights')." SET id_aclsbjgrp=NULL WHERE id_aclsbjgrp=";
        $sql.=$cnx->quote($group);
        $cnx->exec($sql);

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_subject_group')." WHERE id_aclsbjgrp=";
        $sql.=$cnx->quote($group);
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            $output->writeln("Rights: group of subjects '".$group."' is deleted.");
        }
 
    }
}
