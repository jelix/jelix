<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2011-2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\JelixModule\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FilesRights extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('app:filesrights')
            ->setDescription('Set rights and owners on files and directories of the application, according to the configuration in your jelix-scripts.ini.')
            ->setHelp('It could need to launch this command as \'root\' user.')
            ->addArgument(
                'owner',
                InputArgument::OPTIONAL,
                'system user name that will own files'
            )
            ->addArgument(
                'group',
                InputArgument::OPTIONAL,
                'system group name that will own files'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $owner = $input->getArgument('owner');
        $group = $input->getArgument('group');

        $paths = array();
        $paths[] = \jApp::tempBasePath();
        $paths[] = \jApp::logPath();
        $paths[] = \jApp::varPath('mails');
        $paths[] = \jApp::varPath('db');

        foreach ($paths as $path) {
            $this->setRights($path, $owner, $group);
        }
    }

    protected function setRights($path, $owner, $group)
    {
        if ($path == '' || $path == '/' || $path == DIRECTORY_SEPARATOR || !file_exists($path)) {
            return false;
        }
        $config = \jApp::config();
        if (is_file($path)) {
            if ($config->chmodFile) {
                chmod($path, intval($config->chmodFile, 8));
            }

            if ($owner) {
                chown($path, $owner);
            }

            if ($group) {
                chgrp($path, $group);
            }

            return true;
        }

        if (!is_dir($path)) {
            return false;
        }

        if ($config->chmodDir) {
            chmod($path, intval($config->chmodDir, 8));
        }

        if ($owner) {
            chown($path, $owner);
        }

        if ($group) {
            chgrp($path, $group);
        }

        $dir = new \DirectoryIterator($path);
        foreach ($dir as $dirContent) {
            if (!$dirContent->isDot()) {
                $this->setRights($dirContent->getPathName(), $owner, $group);
            }
        }
        unset($dir, $dirContent);

        return true;
    }
}
