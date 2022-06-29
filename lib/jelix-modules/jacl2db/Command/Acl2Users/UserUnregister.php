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

namespace Jelix\Acl2Db\Command\Acl2Users;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UserUnregister extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('acl2user:unregister')
            ->setDescription('Unregister a user from rights system')
            ->setHelp('')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'the login of the user to remove'
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
        $login = $input->getArgument('login');
        if (!$input->getOption('confirm')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Are you sure you want to unregister user '.$login.' from rights system (y/N)?', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('command canceled');

                return 1;
            }
        }

        $cnx = \jDb::getConnection('jacl2_profile');

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_group')
            .' WHERE grouptype=2 and ownerlogin='.$cnx->quote($login);
        $cnx->exec($sql);

        $sql = 'DELETE FROM '.$cnx->prefixTable('jacl2_user_group')
            .' WHERE login='.$cnx->quote($login);
        $cnx->exec($sql);

        if ($output->isVerbose()) {
            $output->writeln("User '".$login."' is unregistered from rights system.");
        }
        return 0;
    }
}
