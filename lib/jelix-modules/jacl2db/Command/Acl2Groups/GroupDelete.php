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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class GroupDelete extends \Jelix\Acl2Db\Command\Acl2\AbstractAcl2Cmd
{
    protected function configure()
    {
        $this
            ->setName('acl2group:delete')
            ->setDescription('Delete a group')
            ->setHelp('')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'the group id to delete'
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
        $id = $this->_getGrpId($input, true);
        if (!$input->getOption('confirm')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('are you sure you want to delete user group '.$id.' (y/N)?', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('command canceled');

                return 1;
            }
        }

        $cnx = \jDb::getConnection('jacl2_profile');

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_rights').' WHERE id_aclgrp='.$cnx->quote($id);
        $cnx->exec($sql);

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_user_group').' WHERE id_aclgrp='.$cnx->quote($id);
        $cnx->exec($sql);

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_group').' WHERE id_aclgrp='.$cnx->quote($id);
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            $output->writeln("Rights: group '".$id."' and all corresponding rights have been deleted.");
        }
        return 0;
    }
}
