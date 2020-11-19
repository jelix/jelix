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

class SubjectGroupCreate extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('acl2:rights-group-create')
            ->setDescription('Add a rights group')
            ->setHelp('')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'Name of the rights group'
            )
            ->addArgument(
                'labelkey',
                InputArgument::REQUIRED,
                'the selector of the label'
            )
            ->addArgument(
                'label',
                InputArgument::REQUIRED,
                'The label of the rights group if the given selector does not exists'
            )
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = $input->getArgument('group');
        $labelkey = $input->getArgument('labelkey');
        $label = $input->getArgument('label');

        $cnx = \jDb::getConnection('jacl2_profile');

        $sql = 'SELECT id_aclsbjgrp FROM '.$cnx->prefixTable('jacl2_subject_group')
            .' WHERE id_aclsbjgrp='.$cnx->quote($group);
        $rs = $cnx->query($sql);
        if ($rs->fetch()) {
            throw new \Exception('This rights group already exists');
        }

        $sql = 'INSERT into '.$cnx->prefixTable('jacl2_subject_group').' (id_aclsbjgrp, label_key) VALUES (';
        $sql .= $cnx->quote($group).',';
        $sql .= $cnx->quote($labelkey);
        $sql .= ')';
        $cnx->exec($sql);

        if ($this->verbose()) {
            $output->writeln("Rights: group of rights '".$group."' is created");
        }

        if ($label &&
            preg_match('/^([a-zA-Z0-9_\\.]+)~([a-zA-Z0-9_]+)\\.([a-zA-Z0-9_\\.]+)$/', $labelkey, $m)) {
            $localestring = "\n".$m[3].'='.$label;
            $path = \jApp::getModulePath($m[1]);
            $file = $path.'locales/'.\jApp::config()->locale.'/'.$m[2].'.'.
                    \jApp::config()->charset.'.properties';
            if (file_exists($file)) {
                $localestring = file_get_contents($file).$localestring;
            }
            file_put_contents($file, $localestring);
            if ($output->isVerbose()) {
                $output->writeln('locale string '.$m[3].' is created into '.$file);
            }
        }
    }
}
