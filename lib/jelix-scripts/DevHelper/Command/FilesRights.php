<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @copyright   2011-2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FilesRights extends \Jelix\DevHelper\AbstractCommand {

    protected function configure()
    {
        $this
            ->setName('app:filesrights')
            ->setDescription('Set rights and owners on files and directories of the application, according to the configuration in your jelix-scripts.ini.')
            ->setHelp('It could need to launch this command as \'root\' user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = array();
        $paths[] = \Jelix\Core\App::tempBasePath();
        $paths[] = \Jelix\Core\App::logPath();
        $paths[] = \Jelix\Core\App::varPath('mails');
        $paths[] = \Jelix\Core\App::varPath('db');

        foreach($paths as $path) {
            $this->setRights($path);
        }
    }

    protected function setRights($path) {

        if ($path == '' || $path == '/' || $path == DIRECTORY_SEPARATOR || !file_exists($path)) {
            return false;
        }

        if (is_file($path)) {
            if ($this->config->doChmod) {
                chmod($path, intval($this->config->chmodFileValue,8));
            }

            if ($this->config->doChown) {
                chown($path, $this->config->chownUser);
                chgrp($path, $this->config->chownGroup);
            }
            return true;
        }

        if (!is_dir($path)) {
            return false;
        }

         if ($this->config->doChmod) {
            chmod($path, intval($this->config->chmodDirValue,8));
         }

         if ($this->config->doChown) {
            chown($path, $this->config->chownUser);
            chgrp($path, $this->config->chownGroup);
         }

        $dir = new \DirectoryIterator($path);
        foreach ($dir as $dirContent) {
            if (!$dirContent->isDot()) {
                $this->setRights($dirContent->getPathName());
            }
        }
        unset($dir);
        unset($dirContent);
        return true;
    }
}
