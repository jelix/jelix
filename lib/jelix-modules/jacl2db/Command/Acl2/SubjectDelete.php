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

class SubjectDelete extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('acl2:right-delete')
            ->setDescription('Delete a right')
            ->setHelp('')
            ->addArgument(
                'right',
                InputArgument::REQUIRED,
                'the right id to delete'
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
        $subject = $input->getArgument('right');
        $cnx = \jDb::getConnection('jacl2_profile');

        if (!$input->getOption('confirm')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Are you sure you want to delete the right '.$subject.' (y/N)?', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('command canceled');

                return 1;
            }
        }

        $sql = 'SELECT id_aclsbj FROM '.$cnx->prefixTable('jacl2_subject')
            .' WHERE id_aclsbj='.$cnx->quote($subject);
        $rs = $cnx->query($sql);
        if (!$rs->fetch()) {
            throw new \Exception('This right does not exist');
        }

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_rights').' WHERE id_aclsbj=';
        $sql .= $cnx->quote($subject);
        $cnx->exec($sql);

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_subject').' WHERE id_aclsbj=';
        $sql .= $cnx->quote($subject);
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            $output->writeln('The right '.$subject.' is deleted');
        }
        return 0;
    }
}
