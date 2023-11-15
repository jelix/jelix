<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\Core\App;
use Jelix\FileUtilities\Directory;
use Jelix\FileUtilities\File;
use Jelix\PropertiesFile\Parser;
use Jelix\PropertiesFile\Properties;

/**
 *
 */
class LocaleCompiler
{

    protected $buildPath;

    function __construct($buildPath) {
        $this->buildPath = $buildPath;
    }

    public function compile($module, $modulePath)
    {
        if (!file_exists($modulePath.'locales/')) {
            return;
        }

        $dir = new \DirectoryIterator($modulePath.'locales/');
        /** @var \SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            $dirName = $fileinfo->getFilename();
            if ($fileinfo->isDir() && preg_match('/^([a-z]+(_[A-Z]+))?$/', $dirName, $m)) {
                $this->compileLang($module, $m[1], $fileinfo->getPathname());
            }
        }
    }

    public function compileLang($module, $lang, $dirPath)
    {
        if (!file_exists($dirPath)) {
            return;
        }

        $dir = new \DirectoryIterator($dirPath);
        /** @var \SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            $file = $fileinfo->getFilename();
            if (str_ends_with($file, '.properties')) {
                if (preg_match('/^(.+)\\.([a-zA-Z0-9_\\-]+)\\.properties$/', $file, $m)) {
                    if ($m[2] != 'UTF-8') {
                        continue;
                    }
                 }
                $this->compileFile($module, $lang, $fileinfo);
            }
        }
    }

    public function compileFile($module, $lang, \SplFileInfo $file) 
    {
        $sourcePath = $this->findPath($module, $lang, $file);
        $properties = new Properties();
        $reader = new Parser();
        $reader->parseFromFile($sourcePath, $properties);
        $this->_strings = $properties->getAllProperties();
        $content = '<?php return '.var_export($this->_strings, true).";\n";

        $cachePath = $this->buildPath.'locales/'.$module.'/'.$lang.'/';
        \jFile::createDir($cachePath);
        \jFile::write($cachePath.$file->getFilename().'.php', $content);
    }


    protected function findPath($module, $locale, \SplFileInfo $file)
    {
        $fileName = $file->getFilename();
        // check if the locale has been overloaded in var/
        $overloadedPath = App::varPath('overloads/'.$module.'/locales/'.$locale.'/'.$fileName);
        if (is_readable($overloadedPath)) {
            return $overloadedPath;
        }

        // check if the locale is available in the locales directory in var/
        $localesPath = App::varPath('locales/'.$locale.'/'.$module.'/locales/'.$fileName);
        if (is_readable($localesPath)) {
            return $localesPath;
        }

        // check if the locale has been overloaded in app/
        $overloadedPath = App::appPath('app/overloads/'.$module.'/locales/'.$locale.'/'.$fileName);
        if (is_readable($overloadedPath)) {
            return $overloadedPath;
        }

        // check if the locale is available in the locales directory in app/
        $localesPath = App::appPath('app/locales/'.$locale.'/'.$module.'/locales/'.$fileName);
        if (is_readable($localesPath)) {
            return $localesPath;
        }

        return $file->getPathname();
    }
    
    
}