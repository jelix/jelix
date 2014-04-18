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

    function onModule($config, $moduleName, $path, $xml) {
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

    function atEnd($config) {
        
    }
}



