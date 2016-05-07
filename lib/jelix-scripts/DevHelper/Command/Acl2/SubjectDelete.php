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
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SubjectDelete  extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('acl2:subject-delete')
            ->setDescription('Delete a subject"')
            ->setHelp('')
            ->addArgument(
                'subject',
                InputArgument::REQUIRED,
                'the subject id to delete'
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

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $subject = $input->getArgument('subject');
        $cnx = \jDb::getConnection('jacl2_profile');

        if (!$input->getOption('confirm')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('are you sure you want to delete subject '.$subject.' (y/N)?', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('command canceled');
                return;
            }
        }

        $sql="SELECT id_aclsbj FROM ".$cnx->prefixTable('jacl2_subject')
            ." WHERE id_aclsbj=".$cnx->quote($subject);
        $rs = $cnx->query($sql);
        if (!$rs->fetch()) {
            throw new \Exception("This subject does not exist");
        }

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_rights')." WHERE id_aclsbj=";
        $sql.=$cnx->quote($subject);
        $cnx->exec($sql);

        $sql="DELETE FROM ".$cnx->prefixTable('jacl2_subject')." WHERE id_aclsbj=";
        $sql.=$cnx->quote($subject);
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            $output->writeln("Rights: subject ".$subject." is deleted");
        }
    }
}
