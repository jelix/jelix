<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\PropertiesFile\Parser;
use Jelix\PropertiesFile\Properties;

/**
 * @internal
 */
class LocaleCompiler
{

    protected $appPath;
    protected $varPath;
    protected $buildPath;

    function __construct($appPath, $varPath, $buildPath)
    {
        $this->appPath = $appPath;
        $this->varPath = $varPath;
        $this->buildPath = $buildPath;
    }


    protected $compiledLangFiles = [];

    /**
     * Compile all locales file of the given module.
     *
     * To be called during the installation of the application for example.
     *
     * Locales are searched into:
     * - var/overloads/ (highest priority)
     * - var/locales/
     * - app/overloads/
     * - app/locales
     * - locales/ into the original module directory (lowest priority)
     *
     * @param string $module module name
     * @param string $modulePath full path to the module
     * @return void
     */
    public function compileModule($module, $modulePath)
    {
        // /!\ Warning, the order is important
        // the locales are tried to be found from the directory having the highest priority
        // to the directory having the lower priority.

        // check if the locale has been overloaded in var/
        $overloadedPath = $this->varPath.'overloads/'.$module.'/locales/';
        if (is_readable($overloadedPath)) {
            $this->compileFromDirectory($module, $overloadedPath, false);
        }

        // check if the locale is available in the locales directory in var/
        $localesPath = $this->varPath.'locales/';
        if (is_readable($localesPath)) {
            $this->compileFromDirectory($module, $localesPath, true);
        }

        // check if the locale has been overloaded in app/
        $overloadedPath = $this->appPath.'app/overloads/'.$module.'/locales/';
        if (is_readable($overloadedPath)) {
            $this->compileFromDirectory($module, $overloadedPath, false);
        }

        // check if the locale is available in the locales directory in app/
        $localesPath = $this->appPath.'app/locales/';
        if (is_readable($localesPath)) {
            $this->compileFromDirectory($module, $localesPath, true);
        }

        if (is_readable($modulePath.'locales/')) {
            $this->compileFromDirectory($module, $modulePath.'locales/', false);
        }

    }

    /**
     * @param string $module module name
     * @param string $directory the path where some locales of the module can be found
     * @param bool $inspectIntoModuleDirectory true if the function should search into a `<module>/locales/` sub-directory
     * @return void
     */
    protected function compileFromDirectory($module, $directory, $inspectIntoModuleDirectory)
    {
        $dir = new \DirectoryIterator($directory);
        /** @var \SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            $dirName = $fileinfo->getFilename();
            if ($fileinfo->isDir() && preg_match('/^([a-z]+(_[A-Z]+))?$/', $dirName, $m)) {
                if ($inspectIntoModuleDirectory) {
                    $localeDirectory = $fileinfo->getPathname().'/'.$module.'/locales/';
                    if (!is_readable($localeDirectory)) {
                        continue;
                    }
                }
                else {
                    $localeDirectory = $fileinfo->getPathname();
                }
                $this->compileLang($module, $m[1], $localeDirectory);
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

                $key = $module.'/'.$lang.'/'.$file;
                if (isset($this->compiledLangFiles[$key])) {
                    // if the file is already compiled, this is because it has been found into a directory
                    // that have a higher priority. So we don't recompile it.
                    // We don't check the cache file, because we don't want to have to delete all cache files
                    // before compiling all locale files.
                    continue;
                }
                $this->compiledLangFiles[$key] = true;

                $this->compileSingleFile($module, $lang, $fileinfo);
            }
        }
    }

    public static function getCacheFileName($module, $lang, $fileName, $buildPath)
    {
        if (preg_match('/^(.+)\\.([a-zA-Z0-9_\\-]+)\\.properties$/', $fileName, $m)) {
            $fileName = $m[1].'.properties';
        }

        return $buildPath.'locales/'.$module.'/'.$lang.'/'.$fileName.'.php';
    }

    protected function getCachePath($module, $lang, \SplFileInfo $file)
    {
        $cachePath = self::getCacheFileName($module, $lang, $file->getFilename(), $this->buildPath);
        \jFile::createDir(dirname($cachePath));
        return $cachePath;
    }

    protected function compileSingleFile($module, $lang, \SplFileInfo $file)
    {
        $cachePath = $this->getCachePath($module, $lang, $file);
        $sourcePath = $file->getPathname();
        $properties = new Properties();
        $reader = new Parser();
        $reader->parseFromFile($sourcePath, $properties);
        $this->_strings = $properties->getAllProperties();
        $content = '<?php return '.var_export($this->_strings, true).";\n";

        \jFile::write($cachePath, $content);
    }


    /**
     * Compile the given locale file from the given module and locale.
     *
     * This method can be called when the locale file has been changed.
     * However, if the given file has been redefined in another directory
     * having a higher priority, it will not be recompiled.
     *
     * Locales are searched into:
     *  - var/overloads/ (highest priority)
     *  - var/locales/
     *  - app/overloads/
     *  - app/locales
     *  - locales/ into the original module directory (lowest priority)
     *
     * @param $module
     * @param $lang
     * @param \SplFileInfo $file
     * @return void
     * @throws \Exception
     */
    public function compileFile($module, $lang, \SplFileInfo $file) 
    {
        $sourcePath = $this->findPath($module, $lang, $file);
        $cachePath = $this->getCachePath($module, $lang, $file);

        $properties = new Properties();
        $reader = new Parser();
        $reader->parseFromFile($sourcePath, $properties);
        $this->_strings = $properties->getAllProperties();
        $content = '<?php return '.var_export($this->_strings, true).";\n";
        \jFile::write($cachePath, $content);
    }


    protected function findPath($module, $locale, \SplFileInfo $file)
    {
        $fileName = $file->getFilename();
        // check if the locale has been overloaded in var/
        $overloadedPath = $this->varPath.'overloads/'.$module.'/locales/'.$locale.'/'.$fileName;
        if (is_readable($overloadedPath)) {
            return $overloadedPath;
        }

        // check if the locale is available in the locales directory in var/
        $localesPath = $this->varPath.'locales/'.$locale.'/'.$module.'/locales/'.$fileName;
        if (is_readable($localesPath)) {
            return $localesPath;
        }

        // check if the locale has been overloaded in app/
        $overloadedPath = $this->appPath.'app/overloads/'.$module.'/locales/'.$locale.'/'.$fileName;
        if (is_readable($overloadedPath)) {
            return $overloadedPath;
        }

        // check if the locale is available in the locales directory in app/
        $localesPath =$this->appPath.'app/locales/'.$locale.'/'.$module.'/locales/'.$fileName;
        if (is_readable($localesPath)) {
            return $localesPath;
        }

        return $file->getPathname();
    }
    
    
}