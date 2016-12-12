<?php
/**
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     MIT
*/
namespace Jelix\DevHelper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommandForApp extends AbstractCommand
{
    /**
     * indicate if the command apply for any entrypoints.
     * Filled by the option reader.
     */
    protected $allEntryPoint = true;

    /**
     * indicate the entry point name on which the command should apply.
     * Filled by the option reader.
     */
    protected $entryPointName = 'index.php';

    /**
     * indicate the entry point id on which the command should apply.
     * Filled by the option reader.
     */
    protected $entryPointId = 'index';

    protected function configure()
    {
        $this
            ->addOption(
               'entry-point',
               'e',
               InputOption::VALUE_REQUIRED,
               'indicate the entry point on which this command should be applied'
            )
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->readEPOption($input);
        $this->loadAppConfig();
        return $this->_execute($input, $output);
    }

    abstract protected function _execute(InputInterface $input, OutputInterface $output);
    
    protected function readEPOption(InputInterface $input)
    {
        // check entry point
        $ep = $input->getOption('entry-point');
        if ($ep) {
            $this->entryPointName = $ep;
            $this->allEntryPoint = false;
            if (($p = strpos($this->entryPointName, '.php')) === false) {
                $this->entryPointId = $this->entryPointName;
                $this->entryPointName .= '.php';
            } else {
                $this->entryPointId = substr($this->entryPointName, 0, $p);
            }
        }
    }

    protected function loadAppConfig()
    {
        if (\Jelix\Core\App::config()) {
            return;
        }

        $ep = $this->getEntryPointInfo($this->entryPointName);
        if ($ep) {
            $configFile = $ep['config'];
        }

        if ($configFile == '') {
            throw new \Exception($this->getName().': Entry point is unknown');
        }

        $compiler = new \Jelix\Core\Config\Compiler($configFile, $this->entryPointName, true);
        \Jelix\Core\App::setConfig($compiler->read(true));
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
        $this->loadAppConfig();

        $config = \Jelix\Core\App::config();
        if (!isset($config->_modulesPathList[$module])) {
            if (isset($config->_externalModulesPathList[$module])) {
                return $config->_externalModulesPathList[$module];
            }
            throw new \Exception($this->getName().": The module $module doesn't exist");
        }

        return $config->_modulesPathList[$module];
    }

    /**
     * @var \Jelix\Core\Infos\AppInfos the content of the project.xml or jelix-app.json file
     */
    protected $appInfos = null;

    /**
     * load the content of the project.xml or jelix-app.json file, and store it
     * into the $appInfos property
     */
    protected function loadAppInfos()
    {
        if ($this->appInfos) {
            return;
        }
        $this->appInfos = new \Jelix\Core\Infos\AppInfos(\Jelix\Core\App::appPath());
  
        $doc = new DOMDocument();
  
        if (!$this->appInfos->exists()){
            throw new Exception($this->name.": cannot load jelix-app.json or project.xml");
        }
    }

    /**
     * @return generator  which returns arrays {'file'=>'', 'config'=>'', 'isCli'=>bool, 'type=>''}
     */
    protected function getEntryPointsList()
    {
        $this->loadAppInfos();
        if (!count($this->appInfos->entrypoints)) {
            return array();
        }
        $generator = function($entrypoints) {
            foreach($entrypoints as $file=>$epElt) {
                $ep = array(
                    'file'=>$epElt["file"],
                    'config'=>$epElt["config"],
                    'isCli'=> ($epElt["type"] == 'cmdline'),
                    'type'=>$epElt["type"],
                );
                if (($p = strpos($ep['file'], '.php')) !== false) {
                    $ep['id'] = substr($ep['file'],0,$p);
                }
                else {
                    $ep['id'] = $ep['file'];
                }
                yield $ep;
            }

        };
        return $generator($this->appInfos->entrypoints);
     }

    protected function getEntryPointInfo($name) {
        $listEp = $this->getEntryPointsList();
        foreach ($listEp as $ep) {
            if ($ep['id'] == $name) {
                return $ep;
            }
        }
        return null;
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
                    $json->extra = (object) array( );
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

    protected function executeSubCommand($name, $arguments, $output) {
        $command = $this->getApplication()->find($name);
        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }
}
