<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @copyright  2011 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
*
* @package    jelix
* @subpackage utils
*/
class jAutoloader {

    protected $nsPaths = array();
    protected $classPaths = array();
    protected $includePaths = array();
    protected $regClassPaths = array();

    /**
     * register a simple class name associated to a path. The class is then
     * supposed to be into a file $includePath.'/'.$className.$extension
     */
    public function registerClass($className, $includePath, $extension='.php') {
        $includePath = rtrim(rtrim($includePath, '/'), '\\');
        $this->classPaths[$className] = $includePath. DIRECTORY_SEPARATOR .str_replace('\\', DIRECTORY_SEPARATOR, $className).$extension;
    }

    /**
     * register a regular expression associated to a path. If the class name match the given
     * regular expression, then it will load the file $includePath.'/'.$className.$extension
     */
    public function registerRegClass($regClassName, $includePath, $extension='.php') {
        $includePath = rtrim(rtrim($includePath, '/'), '\\');
        $this->regClassPaths[$regClassName] = array($includePath, $extension);
    }

    public function registerIncludePath($includePath, $extension='.php') {
        $includePath = rtrim(rtrim($includePath, '/'), '\\');
        $this->includePaths[$includePath] = array($extension, true);
    }

    /**
     * register a namespace associated to a path. The full class path will be resolved
     * following psr0 rules
     * 
     * example: registerNamespace('foo\bar','/my/path', '.php')
     * the resulting path for the class \foo\bar\baz\myclass is /my/path/foo/bar/baz/myclass.php
     */
    public function registerNamespace($namespace, $includePath, $extension='.php') {
        $includePath = rtrim(rtrim($includePath, '/'), '\\');
        $namespace = trim($namespace, '\\');
        if ($namespace == '') {
            $this->includePaths[$includePath] = array($extension, true);
        }
        else
            $this->nsPaths[$namespace] = array($includePath, $extension, true);
    }

    /**
     * register a namespace associated to a path. The full class path will be resolved as:
     *  - the part of the namespace of the class that match $namespace, is removed
     *  - the other part is then transformed following psr0 rules
     *  - the resulting path is then added to $includePath
     * 
     * registerNamespacePathMap('foo\bar','/my/path', '.php');
     * the resulting path for the class \foo\bar\baz\myclass is /my/path/baz/myclass.php
     */
    public function registerNamespacePathMap($namespace, $includePath, $extension='.php') {
        $includePath = rtrim(rtrim($includePath, '/'), '\\');
        $namespace = trim($namespace, '\\');
        if ($namespace == '')
            $this->includePaths[$includePath] = array($extension, false);
        else
            $this->nsPaths[$namespace] = array($includePath, $extension, false);
    }

    /**
     * the method that should be called by the autoload system
     */
    public function loadClass($className) {
        $path = $this->getPath($className);
        if ($path) {
            require($path);
            return true;
        }
        return false;
    }

    /**
     * @return string the full path of the file declaring the given class
     */
    protected function getPath($className) {

        $className = ltrim($className, '\\');

        if (isset($this->classPaths[$className])) {
            return $this->classPaths[$className];
        }

        // namespace mapping

        foreach($this->nsPaths as $ns=>$info) {

            list($incPath, $ext, $psr0) = $info;

            if (strpos($className, $ns) === 0) {
                $path = '';
                $lastNsPos = strripos($className, '\\');
                if ($lastNsPos !== false) {
                    // the class name contains a namespace, let's split ns and class
                    //$namespace = substr($className, $start, $end);
                    $namespace = substr($className, 0, $lastNsPos);
                    if (!$psr0) {
                        // not psr0
                        $namespace = substr($namespace, strlen($ns)+1);
                    }
                    $className = substr($className, $lastNsPos + 1);
                    if ($namespace) {
                        $path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
                    }
                }

                $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . $ext;
                return $incPath.DIRECTORY_SEPARATOR.$path.$fileName;
            }
        }

        foreach($this->includePaths as $incPath=>$info) {
            list($ext, $psr0) = $info;

            $lastNsPos = strripos($className, '\\');
            if ($lastNsPos !== false) {
                // the class name contains a namespace, let's split ns and class
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
            }
            else {
                $namespace = '';
                // the given class name does not contains namespace
            }

            if ($namespace) {
                $path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            else
                $path = '';
            $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . $ext;
            return $incPath.DIRECTORY_SEPARATOR.$path.$fileName;
        }
        foreach ($this->regClassPaths as $reg=>$info) {
            if (preg_match($reg, $className)) {
                list($incPath, $ext) = $info;
                return $incPath. DIRECTORY_SEPARATOR .$className.$ext;
            }
        }
        return '';
    }
}

