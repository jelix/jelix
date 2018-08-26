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
     * indicate the entry point id on which the command should apply.
     * Filled by the option reader.
     */
    protected $selectedEntryPointId = 'index';

    /** @var array list of entry points id on which the command should apply */
    protected $selectedEntryPointsIdList = array();


    private $epOptionName = '';

    private $epListOptionName = '';

    protected function addEpOption($name = 'entry-point', $shortName = 'e') {
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

    protected function addEpListOption($name = 'entry-points', $shortName = 'e') {
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
        parent::execute($input, $output);
        if ($this->epOptionName) {
            $this->selectedEntryPointId = $this->getSelectedEntryPoint($this->epOptionName, $input);
        }
        else if ($this->epListOptionName) {
            $this->selectedEntryPointsIdList = $this->getSelectedEntryPoint($this->epListOptionName, $input, true);
            if (count($this->selectedEntryPointsIdList)) {
                $this->selectedEntryPointId = $this->selectedEntryPointsIdList[0];
            }
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
        else if ($allowList) {
            return array();
        }
        else {
            return 'index';
        }
    }

    private function normalizeEp($ep)
    {
        if (($p = strpos($ep, '.php')) === false) {
            return $ep;
        } else {
            return  substr($ep, 0, $p);
        }
    }

    protected function loadAppConfig($epId = 'index')
    {
        $entrypointList = $this->getEntryPointsList();
        if (!isset($entrypointList[$epId])) {
            throw new \Exception($this->getName().": Entry point $epId is unknown");
        }

        $configFile = $entrypointList[$epId]['config'];

        \jApp::setConfig(\jConfigCompiler::read($configFile, true, true, $entrypointList[$epId]['file']));
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
        $config = \jApp::config();
        if (!$config) {
            $this->loadAppConfig($this->selectedEntryPointId);
        }
        $config = \jApp::config();
        if (!isset($config->_modulesPathList[$module])) {
            throw new \Exception($this->getName().": The module $module doesn't exist");
        }

        return $config->_modulesPathList[$module];
    }

    /**
     * @var \DOMDocument the content of the project.xml file, loaded by loadProjectXml
     */
    protected $projectXml = null;

    /**
     * load the content of the project.xml file, and store the corresponding DOM
     * into the $projectXml property.
     * @return \DOMDocument
     */
    protected function loadProjectXml()
    {
        if ($this->projectXml) {
            return $this->projectXml;
        }

        $doc = new \DOMDocument();

        if (!$doc->load(\jApp::appPath('project.xml'))) {
            throw new \Exception($this->getName().': cannot load project.xml');
        }

        if ($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'project/1.0') {
            throw new \Exception($this->getName().': bad namespace in project.xml');
        }
        $this->projectXml = $doc;
        return $this->projectXml;
    }

    protected function getEntryPointsList()
    {
        $this->loadProjectXml();
        $listEps = $this->projectXml->documentElement->getElementsByTagName('entrypoints');
        if (!$listEps->length) {
            return array();
        }

        $listEp = $listEps->item(0)->getElementsByTagName('entry');
        if (!$listEp->length) {
            return array();
        }

        $list = array();
        for ($i = 0; $i < $listEp->length; ++$i) {
            $epElt = $listEp->item($i);
            $ep = array(
             'file' => $epElt->getAttribute('file'),
             'config' => $epElt->getAttribute('config'),
             'isCli' => ($epElt->getAttribute('type') == 'cmdline'),
             'type' => $epElt->getAttribute('type'),
          );
            if (($p = strpos($ep['file'], '.php')) !== false) {
                $ep['id'] = substr($ep['file'], 0, $p);
            } else {
                $ep['id'] = $ep['file'];
            }

            $list[$ep['id']] = $ep;
        }

        return $list;
    }

    protected function getEntryPointInfo($name)
    {
        $list = $this->getEntryPointsList();
        if (!isset($list[$name])) {
            throw new \Exception($this->getName().": The entry point $name doesn't exist");
        }
        return $list[$name];
    }

    protected function getSupportedJelixVersion()
    {
        $this->loadProjectXml();

        $deps = $this->projectXml->getElementsByTagName('dependencies');
        $minversion = '';
        $maxversion = '';
        if ($deps && $deps->length > 0) {
            $jelix = $deps->item(0)->getElementsByTagName('jelix');
            if ($jelix && $jelix->length > 0) {
                $minversion = $this->fixVersion($jelix->item(0)->getAttribute('minversion'));
                $maxversion = $this->fixVersion($jelix->item(0)->getAttribute('maxversion'));
            }
        }

        return array($minversion, $maxversion);
    }


    protected function registerModulesDir($repository, $repositoryPath)
    {
        $allDirs = \jApp::getDeclaredModulesDir();
        $path = realpath($repositoryPath);
        if ($path == '') {
            throw new \Exception('The modules dir '.$repository.' is not a valid path');
        }
        $path = \Jelix\FileUtilities\Path::shortestPath(\jApp::appPath(), $path);

        $found = false;
        foreach ($allDirs as $dir) {
            $dir = \Jelix\FileUtilities\Path::shortestPath(\jApp::appPath(), $dir);
            if ($dir == $path) {
                $found = true;
                break;
            }
        }
        // the modules dir is not known, we should register it.
        if (!$found) {
            $this->createDir($repositoryPath);
            if (file_exists(\jApp::appPath('composer.json')) && file_exists(\jApp::appPath('vendor'))) {
                // we update composer.json
                $json = json_decode(file_get_contents(\jApp::appPath('composer.json')), false);
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
                file_put_contents(\jApp::appPath('composer.json'), json_encode($json, JSON_PRETTY_PRINT));
                if ($this->verbose()) {
                    $this->output->writeln('<notice>The given modules dir has been added into your composer.json.</notice>');
                }
                $this->output->writeln('<notice>You should launch \'composer update\' to have your module repository recognized.</notice>');
           } elseif (file_exists(\jApp::appPath('application.init.php'))) {
                // we modify the application.init.php directly
                $content = file_get_contents(\jApp::appPath('application.init.php'));
                $content .= "\njApp::declareModulesDir(__DIR__.'/".$path."');\n";
                file_put_contents(\jApp::appPath('application.init.php'), $content);
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
