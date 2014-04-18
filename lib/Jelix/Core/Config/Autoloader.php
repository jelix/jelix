<?php
/**
* @author     Laurent Jouanneau
* @copyright  2012-2014 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

namespace Jelix\Core\Config;

/**
 * Autoloader for informations stored in module.xml files
 */
class Autoloader {


    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * @var object a configuration object readed from an ini file
     * @see Jelix\Core\Config
     */
    protected $config = null;

    /**
     * the method that should be called by the autoload system
     */
    public function loadClass ($className) {
        $path = $this->getPath($className);
        if ($path === false) {
            return false;
        }
        require($path);
    }

    /**
     * @return string the full path of the file declaring the given class
     *              or false if file not found
     */
    protected function getPath($className) {

        if (!$this->config)
            return '';

        $className = ltrim($className, '\\');

        /*
        [_autoload_class]
        className = includefile
        */
        if (isset($this->config->_autoload_class[$className])) {
            return $this->config->_autoload_class[$className];
        }

        $lastNsPos = strripos($className, '\\');
        $path = '';
        if ($lastNsPos !== false) {
            // the class name contains a namespace, let's split ns and class
            $namespace = substr($className, 0, $lastNsPos);
            $class = substr($className, $lastNsPos + 1);
            if ($namespace) {
                $path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
        }
        else {
            $namespace = '';
            $class = &$className;
            // the given class name does not contains namespace
        }
        $fileName = str_replace('_', DIRECTORY_SEPARATOR, $class);

        /*
        [_autoload_namespace]
        namespace = "/path|.ext"
        */
        foreach($this->config->_autoload_namespace as $ns=>$info) {
            if (strpos($className, $ns) === 0) {
                list($incPath, $ext) = explode('|', $info);
                $file = $incPath.DIRECTORY_SEPARATOR.$path.$fileName.$ext;
                if (file_exists($file))
                    return $file;
            }
        }

        /*
        [_autoload_namespacepathmap]
        namespace = "/path|.ext"
        */
        foreach($this->config->_autoload_namespacepathmap as $ns=>$info) {
            if (strpos($className, $ns) === 0) {
                list($incPath, $ext) = explode('|', $info);
                $file = $incPath.DIRECTORY_SEPARATOR.substr($path, strlen($ns)+1).$fileName.$ext;
                if (file_exists($file))
                    return $file;
            }
        }

        /*
        [_autoload_classpattern]
        regexp[] = "regexp"
        path[]= "/path|ext"
        */
        if (isset($this->config->_autoload_classpattern['regexp'])) {
            foreach ($this->config->_autoload_classpattern['regexp'] as $k=>$reg) {
                if (preg_match($reg, $className)) {
                    list($incPath, $ext) = explode('|', $this->config->_autoload_classpattern['path'][$k]);
                    $file = $incPath. DIRECTORY_SEPARATOR .$className.$ext;
                    if (file_exists($file))
                        return $file;
                }
            }
        }

        /*
        [_autoload_includepath]
        path[]="/path|.ext"
        */
        $pathList = array();
        if (isset($this->config->_autoload_includepath['path'])) {
            foreach($this->config->_autoload_includepath['path'] as $info) {
                list($incPath, $ext) = explode('|',$info);
                $file = $incPath.DIRECTORY_SEPARATOR.$path.$fileName.$ext;
                if (file_exists($file))
                    return $file;
            }
        }

        /*
        [_autoload_includepathmap]
        path[]="/path|.ext"
        */
        if (isset($this->config->_autoload_includepathmap['path'])) {
            foreach($this->config->_autoload_includepathmap['path'] as $info) {
                list($incPath, $ext) = explode('|',$info);
                $file = $incPath.DIRECTORY_SEPARATOR.$fileName.$ext;
                if (file_exists($file))
                        return $file;
            }
        }
        return false;
    }
}

