<?php
/**
 * @package      jelix
 * @subpackage   core_config_plugin
 *
 * @author       Laurent Jouanneau
 * @copyright    2012 Laurent Jouanneau
 *
 * @see         http://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class nsautoloaderConfigCompilerPlugin implements \Jelix\Core\Config\CompilerPluginInterface
{
    public function getPriority()
    {
        return 1;
    }

    public function atStart($config)
    {
        $config->_autoload_class = array();
        $config->_autoload_namespacepsr0 = array(); // psr0
        $config->_autoload_classpattern = array();
        $config->_autoload_includepathmap = array();
        $config->_autoload_includepath = array();
        $config->_autoload_namespacepsr4 = array(); // psr4
        $config->_autoload_autoloader = array();
        $config->_autoload_fallback = array('psr4' => array(), 'psr0' => array());
    }

    public function onModule($config, Jelix\Core\Infos\ModuleInfos $module)
    {
        $modulePath = $module->getItemPath();
        $moduleFile = $module->getFilePath();

        foreach ($module->autoloaders as $path) {
            $p = $modulePath.$path;
            if (!file_exists($p)) {
                throw new Exception('Error in autoload configuration -- In '.$modulePath.'/'.$moduleFile.', this autoloader doesn\'t exists: '.$p);
            }
            $config->_autoload_autoloader[] = $p;
        }

        foreach ($module->autoloadIncludePath as $path) {
            if (is_array($path)) {
                $p = $modulePath.$path[0];
                $finalpath = $modulePath.join('|', $path);
            } else {
                $p = $modulePath.$path;
                $finalpath = $p.'|.php';
            }

            if (!file_exists($p)) {
                throw new Exception('Error in autoload configuration -- In '.$modulePath.'/'.$moduleFile.', this directory for includePath doesn\'t exists: '.$p);
            }
            if (!isset($config->_autoload_includepath['path'])) {
                $config->_autoload_includepath['path'] = array();
            }
            $config->_autoload_includepath['path'][] = $finalpath;
        }

        foreach ($module->autoloadClasses as $className => $path) {
            $p = $modulePath.$path;
            if (!file_exists($p)) {
                throw new Exception('Error in autoload configuration -- In '.$modulePath.'/'.$moduleFile.', this class file doesn\'t exists: '.$p);
            }
            $config->_autoload_class[$className] = $p;
        }

        foreach ($module->autoloadClassPatterns as $pattern => $path) {
            if (is_array($path)) {
                $p = $modulePath.$path[0];
                $finalpath = $modulePath.join('|', $path);
            } else {
                $p = $modulePath.$path;
                $finalpath = $p.'|.php';
            }
            if (!file_exists($p)) {
                throw new Exception('Error in the autoload configuration -- In '.$modulePath.'/'.$moduleFile.', this directory for classPattern doesn\'t exists: '.$p);
            }
            if (!isset($config->_autoload_classpattern['regexp'])) {
                $config->_autoload_classpattern['regexp'] = array();
                $config->_autoload_classpattern['path'] = array();
            }
            $config->_autoload_classpattern['regexp'][] = $pattern;
            $config->_autoload_classpattern['path'][] = $finalpath;
        }

        $processNs = function ($ns, $path) use ($modulePath, $moduleFile, $config) {
            if (is_array($path)) {
                $p = $modulePath.$path[0];
                $finalpath = $modulePath.join('|', $path);
            } else {
                $p = $modulePath.$path;
                $finalpath = $p.'|.php';
            }
            if (!file_exists($p)) {
                throw new Exception('Error in the autoload configuration -- In '.$modulePath.'/'.$moduleFile.', this directory for namespace psr0 doesn\'t exists: '.$p);
            }
            if ($ns === 0) {
                $config->_autoload_fallback['psr0'][] = $finalpath;
            } else {
                $config->_autoload_namespacepsr0[$ns][] = $finalpath;
            }
        };

        if (isset($module->autoloadPsr0Namespaces[0])) {
            foreach ($module->autoloadPsr0Namespaces[0] as $path) {
                $processNs(0, $path);
            }
        }

        foreach ($module->autoloadPsr0Namespaces as $ns => $pathList) {
            if ($ns === 0) {
                continue;
            }
            foreach ($pathList as $path) {
                $processNs($ns, $path);
            }
        }

        $processNs2 = function ($ns, $path) use ($modulePath, $moduleFile, $config) {
            if (is_array($path)) {
                $p = $modulePath.$path[0];
                $finalpath = $modulePath.join('|', $path);
            } else {
                $p = $modulePath.$path;
                $finalpath = $p.'|.php';
            }
            if (!file_exists($p)) {
                throw new Exception('Error in the autoload configuration -- In '.$modulePath.'/'.$moduleFile.', this directory for namespace psr4 doesn\'t exists: '.$p);
            }
            if ($ns === 0) {
                $config->_autoload_fallback['psr4'][] = $finalpath;
            } else {
                $config->_autoload_namespacepsr4[$ns][] = $finalpath;
            }
        };

        if (isset($module->autoloadPsr4Namespaces[0])) {
            foreach ($module->autoloadPsr4Namespaces[0] as $path) {
                $processNs2(0, $path);
            }
        }

        foreach ($module->autoloadPsr4Namespaces as $ns => $pathList) {
            if ($ns === 0) {
                continue;
            }
            foreach ($pathList as $path) {
                $processNs2($ns, $path);
            }
        }
    }

    public function atEnd($config)
    {
        // namespace of compiled components
        $config->_autoload_namespacepsr4['Jelix\\BuiltComponents'][] = \Jelix\Core\App::buildPath().'|.php';
    }
}
