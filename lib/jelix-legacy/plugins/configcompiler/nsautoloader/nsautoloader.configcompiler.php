<?php
/**
* @package      jelix
* @subpackage   core
* @author       Laurent Jouanneau
* @copyright    2012 Laurent Jouanneau
* @link         http://jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class nsautoloaderConfigCompilerPlugin implements \Jelix\Core\Config\CompilerPluginInterface {

    function getPriority() {
        return 1;
    }

    function atStart($config) {
        $config->_autoload_class = array();
        $config->_autoload_namespace = array(); // psr0
        $config->_autoload_classpattern = array();
        $config->_autoload_includepathmap = array();
        $config->_autoload_includepath = array();
        $config->_autoload_namespacepathmap = array(); // psr4
        $config->_autoload_autoloader = array();
        $config->_autoload_fallback = array('psr4'=>array(), 'psr0'=>array());
    }

    function onModule($config, $moduleName, $path, $moduleInfo, $isXml = false) {
        if ($isXml) {
            // we have a deprecated module.xml file
            $this->_onModuleXml($config, $moduleName, $path, $moduleInfo);
            return;
        }
        if (\Jelix\Core\Config\ComposerUtils::isLoaded($moduleInfo)) {
            return;
        }
        $this->_onModuleComposer($config, $moduleName, $path, $moduleInfo);
    }

    /**
     * @deprecated
     */
    function _onModuleXml($config, $moduleName, $path, $xml) {
        if (!isset($xml->autoload))
            return;
        foreach($xml->autoload->children() as $type=>$element) {
            if (isset($element['suffix']))
                $suffix = '|'.(string)$element['suffix'];
            else
                $suffix = '|.php';
            switch ($type) {
                case 'class':
                    $p = $path.((string)$element['file']);
                    if (!file_exists($p))
                        throw new Exception ('Error in autoload configuration -- In '.$path.'/module.xml, this class file doesn\'t exists: '.$p);
                    $config->_autoload_class[(string)$element['name']] = $p;
                    break;
                case 'classPattern':
                    $p = $path.((string)$element['dir']);
                    if (!file_exists($p))
                        throw new Exception ('Error in the autoload configuration -- In '.$path.'/module.xml, this directory for classPattern doesn\'t exists: '.$p);
                    if (!isset($config->_autoload_classpattern['regexp'])) {
                        $config->_autoload_classpattern['regexp'] = array();
                        $config->_autoload_classpattern['path'] = array();
                    }
                    $config->_autoload_classpattern['regexp'][] = (string)$element['pattern'];
                    $config->_autoload_classpattern['path'][] =  $p.$suffix;
                    break;
                case 'namespace':
                    $p = $path.((string)$element['dir']);
                    if (!file_exists($p))
                        throw new Exception ('Error in the autoload configuration -- In '.$path.'/module.xml, this directory for namespace doesn\'t exists: '.$p);
                    $name = trim((string)$element['name'],'\\');
                    if ($name == '') {
                        $config->_autoload_fallback['psr0'][] = $p.$suffix;
                    }
                    else {
                        $config->_autoload_namespace[$name] = $p.$suffix;
                    }
                    break;
                case 'namespacePathMap':
                    $p = $path.((string)$element['dir']);
                    if (!file_exists($p))
                        throw new Exception ('Error in autoload configuration -- In '.$path.'/module.xml, this directory for namespacePathMap doesn\'t exists: '.$p);
                    $name = trim((string)$element['name'],'\\');
                    if ($name == '') {
                        $config->_autoload_fallback['psr4'][] = $p.$suffix;
                    }
                    else {
                        $config->_autoload_namespacepathmap[$name] = $p.$suffix;
                    }
                    break;
                case 'includePath':
                    $p = $path.((string)$element['dir']);
                    if (!file_exists($p))
                        throw new Exception ('Error in autoload configuration -- In '.$path.'/module.xml, this directory for includePath doesn\'t exists: '.$p);
                    if (!isset($config->_autoload_includepath['path'])) {
                        $config->_autoload_includepath['path'] = array();
                    }
                    $config->_autoload_includepath['path'][] =  $p.$suffix;
                    break;
                case 'autoloader':
                    $p = $path.((string)$element['file']);
                    if (!file_exists($p))
                        throw new Exception ('Error in autoload configuration -- In '.$path.'/module.xml, this autoloader doesn\'t exists: '.$p);
                    $config->_autoload_autoloader[] = $p;
                    break;
            }
        }
    }

    function _onModuleComposer($config, $moduleName, $path, $composer) {

        if (!isset($composer->autoload)) {
            return;
        }

        $suffix = '|.php';
        if (isset($composer->autoload->{"psr-4"})) {
            foreach($composer->autoload->{"psr-4"} as $prefix=>$p) {
                if (is_array($p)) {
                    foreach($p as $p2) {
                        $p2 = $path.$p2;
                        if (!file_exists($p2))
                            throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this directory for psr-4 doesn\'t exists: '.$p2);
                        $name = trim($prefix);
                        $config->_autoload_namespacepathmap[$name][] = $p2.$suffix;
                    }
                }
                else {
                    $p = $path.$p;
                    if (!file_exists($p))
                        throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this directory for psr-4 doesn\'t exists: '.$p);
                    $name = trim($prefix);
                    if ($name == '') {
                        $config->_autoload_fallback['psr4'][] = $p.$suffix;
                    }
                    else {
                        $config->_autoload_namespacepathmap[$name] = $p.$suffix;
                    } 
                }
            }
        }

        if (isset($composer->autoload->{"psr-0"})) {
            foreach($composer->autoload->{"psr-0"} as $prefix=>$p) {
                $name = trim($prefix);
                if (is_array($p)) {
                    foreach($p as $p2) {
                        $p2 = $path.$p2;
                        if (!file_exists($p2))
                            throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this directory for psr-0 doesn\'t exists: '.$p2);
                        $config->_autoload_namespace[$name][] = $p2.$suffix;
                    }
                }
                else {
                    if ($p == '') {
                        //FIXME UniqueGlobalClass
                    }
                    $p = $path.$p;
                    if (!file_exists($p))
                        throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this directory for psr-0 doesn\'t exists: '.$p);
                    if ($name === '' || $name == '_empty_') {
                        $config->_autoload_fallback['psr0'][] = $p.$suffix;
                    }
                    else {
                        $config->_autoload_namespace[$name] = $p.$suffix;
                    }
                }
            }
        }

        if (isset($composer->autoload->{"include-path"})) {
            foreach($composer->autoload->{"include-path"} as $p) {
                if (is_array($p)) {
                    foreach($p as $p2) {
                        $p2 = $path.$p2;
                        if (!file_exists($p2))
                            throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this directory for include-path doesn\'t exists: '.$p2);
                        $config->_autoload_includepath['path'][] = $p2.$suffix;
                    }
                }
                else {
                    $p = $path.$p;
                    if (!file_exists($p))
                        throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this directory for include-path doesn\'t exists: '.$p);
                    $config->_autoload_includepath['path'][] = $p.$suffix;
                }
            }
        }

        if (isset($composer->autoload->files)) {
            foreach($composer->autoload->files as $p) {
                if (is_array($p)) {
                    foreach($p as $p2) {
                        $p2 = $path.$p2;
                        if (!file_exists($p2))
                            throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this file doesn\'t exists: '.$p2);
                        $config->_autoload_autoloader[] = $p2;
                    }
                }
                else {
                    $p = $path.$p;
                    if (!file_exists($p))
                        throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this file doesn\'t exists: '.$p);
                    $config->_autoload_autoloader[] = $p;
                }
            }
        }

        if (isset($composer->autoload->classmap)) {
            $processClassMap = function ($p) use($path, $config) {
                $p = $path.$p;
                if (!file_exists($p))
                    throw new Exception ('Error in autoload configuration -- In '.$path.'/composer.json, this path in classmap doesn\'t exists: '.$p);
                $map = \Jelix\External\ClassMapGenerator::createMap($p);
                $config->_autoload_class = array_merge($config->_autoload_class, $map);
            };
            foreach($composer->autoload->classmap as $p) {
                if (is_array($p)) {
                    foreach($p as $p2) {
                        $processClassMap($p2);
                    }
                }
                else {
                    $processClassMap($p);
                }
            }
        }
    }

    function atEnd($config) {
        
    }
}



