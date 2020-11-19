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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RemoveRight extends AbstractAcl2Cmd
{
    protected function configure()
    {
        $this
            ->setName('acl2:remove')
            ->setDescription('Remove a right')
            ->setHelp('')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'group id'
            )
            ->addArgument(
                'right',
                InputArgument::REQUIRED,
                'The name of the right'
            )
            ->addArgument(
                'resource',
                InputArgument::OPTIONAL,
                'the resource value',
                '-'
            )
            ->addOption(
                'allres',
                null,
                InputOption::VALUE_NONE,
                'remove also all resource rights'
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
        $cnx = \jDb::getConnection('jacl2_profile');

        $group = $cnx->quote($this->_getGrpId($input));
        $subject = $cnx->quote($input->getArgument('right'));
        $resource = $cnx->quote($input->getArgument('resource'));
        $allResource = $input->getOption('allres');

        if (!$input->getOption('confirm')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('are you sure you want to delete right (y/N)?', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('command canceled');

                return;
            }
        }

        $sql = 'SELECT * FROM '.$cnx->prefixTable('jacl2_rights').'
                WHERE id_aclgrp='.$group.'
                AND id_aclsbj='.$subject;
        if (!$allResource) {
            $sql .= ' AND id_aclres='.$resource;
        }

        $rs = $cnx->query($sql);
        if (!$rs->fetch()) {
            throw new \Exception('Error: this right is not set');
        }

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_rights').'
             WHERE id_aclgrp='.$group.'
                AND id_aclsbj='.$subject;
        if (!$allResource) {
            $sql .= ' AND id_aclres='.$resource;
        }
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            if ($allResource) {
                $output->writeln("Right {$subject} have been deleted on group {$group} ");
            } else {
                $output->writeln("Right {$subject} have been deleted on group {$group} and resource {$resource}");
            }
        }
    }
}
