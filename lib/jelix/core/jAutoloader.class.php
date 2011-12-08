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
    protected $regClassPaths = array();

    /**
     * register a simple class name associated to a path. The class is then
     * supposed to be into a file $includePath.'/'.$className.$extension
     */
    public function registerClass($className, $includePath, $extension='.php') {
        $this->classPaths[$className] = array($includePath, $extension);
    }

    /**
     * register a regular expression associated to a path. If the class name match the given
     * regular expression, then it will load the file $includePath.'/'.$className.$extension
     */
    public function registerRegClass($regClassName, $includePath, $extension='.php') {
        $this->regClassPaths[$regClassName] = array($includePath, $extension);
    }

    /**
     * register a namespace associated to a path. If psr0 is true, the path will be resolved
     * following psr0 rules, else it will be resolved as:
     *  - the part of the namespace of the class that match $namespace, is removed
     *  - the other part is then transformed following psr0 rules
     *  - the resulting path is then added to $includePath
     * example: registerNamespace('\foo\bar','/my/path', '.php', true)
     * the resulting path for the class \foo\bar\baz\myclass is /my/path/foo/bar/baz/myclass.php
     * 
     * registerNamespace('\foo\bar','/my/path', '.php', false);
     * the resulting path for the class \foo\bar\baz\myclass is /my/path/baz/myclass.php
     */
    public function registerNamespace($namespace, $includePath, $extension='.php', $psr0=true) {
        $this->nsPaths[$namespace] = array($includePath, $extension, $psr0);
    }

    public function loadClass($className) {
        $path = $this->getPath($className);
        if ($path) {
            require($path);
            return true;
        }
        return false;
    }

    protected function getPath($className) {

        $includePath = '';

        foreach($this->nsPaths as $ns=>$info) {
            // $info: 0=>include path  1=>extension 2=>true=psr0

            // check if the given class corresponds to the registred namespace\class
            if ($ns == $className) {
                $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . $info[1];
                $includePath = $info[0].$fileName;
                break;
            }

            // check if the given class name begins with the current namespace
            if (strpos($className, $ns) === 0) {
                $namespace = '';
                $lastNsPos = strripos($className, '\\');
                if ($lastNsPos !== false) {
                    // the class name contains a namespace, let's split ns and class
                    $namespace = substr($className, 0, $lastNsPos);
                    $className = substr($className, $lastNsPos + 1);
                }
                else {
                    // the given class name does not contains namespace
                }

                if ($namespace) {
                    if (!$info[2]) {
                        // not psr0
                        $namespace = substr($namespace, strlen($ns));
                    }
                    $path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
                }
                else
                    $path = '';
                $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . $info[1];
                $includePath = $info[0].$path.$fileName;
                break;
            }
        }

        return $includePath;
    }
}

