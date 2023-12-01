<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\DevHelper;

use Jelix\Core\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommandForApp extends AbstractCommand
{
    /**
     * indicate if the command apply for any entrypoints.
     * Filled by the option reader.
     */
    protected $allEntryPoint = true;

    /**
     * indicate the entry point id on which the command should apply.
     * Filled by the option reader.
     */
    protected $selectedEntryPointId = '';

    /** @var array list of entry points id on which the command should apply */
    protected $selectedEntryPointsIdList = array();

    private $epOptionName = '';

    private $epListOptionName = '';

    protected function addEpOption($name = 'entry-point', $shortName = 'e')
    {
        $this->epOptionName = $name;
        $this
            ->addOption(
                $name,
                $shortName,
                InputOption::VALUE_REQUIRED,
                'indicate the entry point on which this command should be applied'
            )
        ;
    }

    protected function addEpListOption($name = 'entry-points', $shortName = 'e')
    {
        $this->epListOptionName = $name;
        $this
            ->addOption(
                $name,
                $shortName,
                InputOption::VALUE_REQUIRED,
                'indicate the list of entry point (names separated by a coma) on which this command should be applied'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = parent::execute($input, $output);
        if ($code) {
            return $code;
        }
        if ($this->epOptionName) {
            $this->selectedEntryPointId = $this->getSelectedEntryPoint($this->epOptionName, $input);
        } elseif ($this->epListOptionName) {
            $this->selectedEntryPointsIdList = $this->getSelectedEntryPoint($this->epListOptionName, $input, true);
            if (count($this->selectedEntryPointsIdList)) {
                $this->selectedEntryPointId = $this->selectedEntryPointsIdList[0];
            }
        }

        if ($this->selectedEntryPointId == '') {
            $entrypoint = $this->getFrameworkInfos()->getDefaultEntryPointInfo();
            $this->selectedEntryPointId = $entrypoint->getId();
        }

        $this->loadAppConfig($this->selectedEntryPointId);

        return $this->_execute($input, $output);
    }

    abstract protected function _execute(InputInterface $input, OutputInterface $output);

    protected function getSelectedEntryPoint($optionName, InputInterface $input, $allowList = false)
    {
        // check entry point
        $ep = $input->getOption($optionName);
        if ($ep) {
            $this->allEntryPoint = false;

            if ($allowList) {
                $list = preg_split('/\s*,\s*/', $ep);

                return array_map(array($this, 'normalizeEp'), $list);
            }

            return $this->normalizeEp($ep);
        }
        if ($allowList) {
            return array();
        }

        return '';
    }

    private function normalizeEp($ep)
    {
        if (($p = strpos($ep, '.php')) === false) {
            return $ep;
        }

        return substr($ep, 0, $p);
    }

    protected function loadAppConfig($epId)
    {
        $entrypoint = $this->getFrameworkInfos()->getEntryPointInfo($epId);
        if (!$entrypoint) {
            throw new \Exception($this->getName().": Entry point {$epId} is unknown");
        }

        $configFile = $entrypoint->getConfigFile();

        $compiler = new \Jelix\Core\Config\Compiler($configFile, $entrypoint->getFile());
        \Jelix\Core\App::setConfig($compiler->read(true));
        \jFile::createDir(App::tempPath(), App::config()->chmodDir);
    }

    /**
     * helper method to retrieve the path of the module.
     *
     * @param string $module the name of the module
     *
     * @return string the path of the module
     */
    protected function getModulePath($module)
    {
        $config = App::config();
        if (!$config) {
            $this->loadAppConfig($this->selectedEntryPointId);
            $config = App::config();
        }

        if (!isset($config->_modulesPathList[$module])) {
            throw new \Exception($this->getName().": The module {$module} doesn't exist");
        }

        return $config->_modulesPathList[$module];
    }

    protected function getFrameworkInfos()
    {
        return \Jelix\Core\Infos\FrameworkInfos::load();
    }

    /**
     * @param string $name the entry point name
     *
     * @throws \Exception
     *
     * @return \Jelix\Core\Infos\EntryPoint
     */
    protected function getEntryPointInfo($name)
    {
        $ep = $this->getFrameworkInfos()->getEntryPointInfo($name);

        if (!$ep) {
            throw new \Exception($this->getName().": The entry point {$name} doesn't exist");
        }

        return $ep;
    }

    protected function registerModulesDir($repository, $repositoryPath)
    {
        $allDirs = \Jelix\Core\App::getDeclaredModulesDir();
        $path = realpath($repositoryPath);
        if ($path == '') {
            throw new \Exception('The modules dir '.$repository.' is not a valid path');
        }
        $path = \Jelix\FileUtilities\Path::shortestPath(\Jelix\Core\App::appPath(), $path);

        $found = false;
        foreach ($allDirs as $dir) {
            $dir = \Jelix\FileUtilities\Path::shortestPath(\Jelix\Core\App::appPath(), $dir);
            if ($dir == $path) {
                $found = true;

                break;
            }
        }
        // the modules dir is not known, we should register it.
        if (!$found) {
            $this->createDir($repositoryPath);
            if (file_exists(\Jelix\Core\App::appPath('composer.json')) && file_exists(\Jelix\Core\App::appPath('vendor'))) {
                // we update composer.json
                $json = json_decode(file_get_contents(\Jelix\Core\App::appPath('composer.json')), false);
                if (!$json) {
                    throw new \Exception('composer.json has bad json format');
                }
                if (!property_exists($json, 'extra')) {
                    $json->extra = (object) array();
                }
                if (!property_exists($json->extra, 'jelix')) {
                    $json->extra->jelix = (object) array('modules-dir' => array());
                } elseif (!property_exists($json->extra->jelix, 'modules-dir')) {
                    $json->extra->jelix->{'modules-dir'} = array();
                }
                $json->extra->jelix->{'modules-dir'}[] = $path;
                file_put_contents(\Jelix\Core\App::appPath('composer.json'), json_encode($json, JSON_PRETTY_PRINT));
                if ($this->verbose()) {
                    $this->output->writeln('<notice>The given modules dir has been added into your composer.json.</notice>');
                }
                $this->output->writeln('<notice>You should launch \'composer update\' to have your module repository recognized.</notice>');
            } elseif (file_exists(\Jelix\Core\App::appPath('application.init.php'))) {
                // we modify the application.init.php directly
                $content = file_get_contents(\Jelix\Core\App::appPath('application.init.php'));
                $content .= "\njApp::declareModulesDir(__DIR__.'/".$path."');\n";
                file_put_contents(\Jelix\Core\App::appPath('application.init.php'), $content);
                if ($this->verbose()) {
                    $this->output->writeln('<notice>The given modules dir has been added into your application.init.php.</notice>');
                }
            }
        }
    }

    protected function executeSubCommand($name, $arguments, $output)
    {
        $command = $this->getApplication()->find($name);
        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }
}
