<?php
/**
 * @package     jelix-scripts
 *
 * @author      Bisse Romain
 * @contributor Laurent Jouanneau
 *
 * @copyright   2009 Bisse Romain, 2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateClassFromDao extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('module:create-class-dao')
            ->setDescription('Allow to create a class into classes directory from a *dao.xml file.')
            ->setHelp('')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the module'
            )
            ->addArgument(
                'classname',
                InputArgument::REQUIRED,
                'The name of the class to generate'
            )
            ->addArgument(
                'daoname',
                InputArgument::REQUIRED,
                'the name of the dao from which the class will be generated'
            )
            ->addOption(
                'profile',
                null,
                InputOption::VALUE_REQUIRED,
                'indicate the name of the profile to use for the database connection',
                ''
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $daoname = $input->getArgument('daoname');
        $classname = $input->getArgument('classname');
        $profileName = $input->getOption('profile');

        // Computing some paths and filenames

        $modulePath = $this->getModulePath($module);

        $targetClassPath = $modulePath.'classes/';
        $targetClassPath .= strtolower($classname).'.class.php';

        // Parsing the dao xml file

        $selector = new \jSelectorDao($module.'~'.$daoname, $profileName);
        $cnt = \jDb::getConnection($profileName);
        $context = new \jDaoContext($profileName, $cnt);
        $compiler = new \Jelix\Dao\Generator\Compiler();
        $parser = $compiler->parse($selector, $context);

        $properties = $parser->getProperties();

        // Generating the class

        $classContent = '';
        foreach ($properties as $name => $property) {
            $classContent .= "    public \${$name};\n";
        }
        $this->createFile(
            $targetClassPath,
            'module/classfromdao.class.tpl',
            array('properties' => $classContent,
                'name' => $classname, ),
            'Class'
        );
    }
}
