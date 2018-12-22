<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @copyright   2011-2018 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\JelixModule\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FilesRights extends \Jelix\Scripts\ModuleCommandAbstract {

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
        $paths[] = \jApp::tempBasePath();
        $paths[] = \jApp::logPath();
        $paths[] = \jApp::varPath('mails');
        $paths[] = \jApp::varPath('db');
        
        foreach($paths as $path) {
            $this->setRights($path);
        }
    }

    protected function setRights($path) {

        if ($path == '' || $path == '/' || $path == DIRECTORY_SEPARATOR || !file_exists($path)) {
            return false;
        }
        $config = \jApp::config();
        if (is_file($path)) {
            if ($config->doChmod) {
                chmod($path, intval($config->chmodFileValue,8));
            }

            if ($config->doChown) {
                chown($path, $config->chownUser);
                chgrp($path, $config->chownGroup);
            }
            return true;
        }

        if (!is_dir($path)) {
            return false;
        }

         if ($config->doChmod) {
            chmod($path, intval($config->chmodDirValue,8));
         }

         if ($config->doChown) {
            chown($path, $config->chownUser);
            chgrp($path, $config->chownGroup);
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
