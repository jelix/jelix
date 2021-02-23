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
use Symfony\Component\Console\Output\OutputInterface;

class AddRight extends AbstractAcl2Cmd
{
    protected function configure()
    {
        $this
            ->setName('acl2:add')
            ->setDescription('Add a right')
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
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cnx = \jDb::getConnection('jacl2_profile');

        $group = $cnx->quote($this->_getGrpId($input));

        $subject = $cnx->quote($input->getArgument('right'));
        $resource = $cnx->quote($input->getArgument('resource'));

        $sql = 'SELECT * FROM '.$cnx->prefixTable('jacl2_rights').'
                WHERE id_aclgrp='.$group.'
                AND id_aclsbj='.$subject.'
                AND id_aclres='.$resource;
        $rs = $cnx->query($sql);
        if ($rs->fetch()) {
            throw new \Exception('right already set');
        }

        $sql = 'SELECT * FROM '.$cnx->prefixTable('jacl2_subject').' WHERE id_aclsbj='.$subject;
        $rs = $cnx->query($sql);
        if (!($sbj = $rs->fetch())) {
            throw new \Exception('right is unknown');
        }

        $sql = 'INSERT into '.$cnx->prefixTable('jacl2_rights')
            .' (id_aclgrp, id_aclsbj, id_aclres) VALUES (';
        $sql .= $group.',';
        $sql .= $subject.',';
        $sql .= $resource.')';

        $cnx->exec($sql);
        if ($output->isVerbose()) {
            $output->writeln("Right {$subject} is added on group {$group} and resource {$resource}");
        }
    }
}
