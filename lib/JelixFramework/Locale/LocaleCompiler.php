<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023-2024 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;
use Jelix\PropertiesFile\Parser;
use Jelix\PropertiesFile\Properties;

/**
 * @internal
 */
class LocaleCompiler
{

    function __construct(
        protected string $appPath,
        protected string $varPath,
        protected array $otherPath,
        protected string $buildPath
    )
    {
    }

    protected $compiledLangFiles = [];

    /**
     * Compile all locales files of the given module.
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

        // check if the locale is available in other locales directory from packages
        foreach ($this->otherPath as $dir) {
            $this->compileFromDirectory($module, $dir, true);
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

    /**
     * Compile properties files from a single directory, corresponding to a specific lang and a specific module
     *
     * @param string $module
     * @param string $lang
     * @param string $dirPath full path of the directory
     * @return void
     */
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

                $this->compileFile($module, $lang, $fileinfo);
            }
        }
    }

    /**
     * Gives the path of the file that is the result of the compilation of a properties file.
     *
     * @param string $module
     * @param string $lang
     * @param string $fileName the file name (not the path)
     * @param string $buildPath the main build directory where compiled files are stored.
     * @return string the full path of the compiled file.
     */
    public static function getCacheFileName($module, $lang, $fileName, $buildPath)
    {
        if (preg_match('/^(.+)\\.([a-zA-Z0-9_\\-]+)\\.properties$/', $fileName, $m)) {
            $fileName = $m[1].'.properties';
        }

        return $buildPath.'locales/'.$module.'/'.$lang.'/'.$fileName.'.php';
    }

    protected function getCachePath($module, $lang, $fileName)
    {
        $cachePath = self::getCacheFileName($module, $lang, $fileName, $this->buildPath);
        \jFile::createDir(dirname($cachePath));
        return $cachePath;
    }

    /**
     * Compile one properties file and store the result into the build directory
     *
     * @param string $module
     * @param string $lang
     * @param \SplFileInfo $file
     * @return void
     * @throws \Exception
     */
    protected function compileFile($module, $lang, \SplFileInfo $file)
    {
        $cachePath = $this->getCachePath($module, $lang, $file->getFilename());
        $sourcePath = $file->getPathname();
        $properties = new Properties();
        $reader = new Parser();
        $reader->parseFromFile($sourcePath, $properties);
        $strings = $properties->getAllProperties();
        $content = '<?php return '.var_export($strings, true).";\n";

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
     * @param FilePlace $filename
     * @return void
     * @throws \Exception
     */
    public function compileSingleFile(FilePlace $file)
    {
        $moduleLocale = $this->findPriorityPath($file);
        if ($moduleLocale === false) {
            return;
        }
        list($module, $locale, $filename) = $moduleLocale;;
        $cachePath = $this->getCachePath($module, $locale, $filename);

        $properties = new Properties();
        $reader = new Parser();
        $reader->parseFromFile($file->filePath, $properties);
        $strings = $properties->getAllProperties();
        $content = '<?php return '.var_export($strings, true).";\n";
        \jFile::write($cachePath, $content);
    }


    /**
     * @param FilePlace $file
     * @return array|false  [module, locale, filename]
     */
    protected function getModuleAndLocale(FilePlace $file)
    {
        if ($file->place == FilePlaceEnum::VarOverloads ||
            $file->place == FilePlaceEnum::AppOverloads ||
            $file->place == FilePlaceEnum::Module
        ) {
            if (preg_match('!^locales/([^/]+)/(.+)!', $file->subPath, $m)) {
                return [$file->module, $m[1], $m[2]];
            }
            return false;
        }

        if ($file->place == FilePlaceEnum::Var || $file->place == FilePlaceEnum::App) {
            if (preg_match('!^locales/([^/]+)/([^/]+)/locales/(.+)!', $file->subPath, $m)) {
                return [$m[2], $m[1], $m[3]];
            }
            return false;
        }

        return false;
    }

    /**
     * @param string $module
     * @param string $locale
     * @param FilePlace $file
     * @return array|null  [module, locale, filename]
     */
    protected function findPriorityPath(FilePlace $file)
    {
        $moduleLocale = $this->getModuleAndLocale($file);
        if ($moduleLocale === false) {
            return null;
        }
        if ($file->place == FilePlaceEnum::VarOverloads) {
           return $moduleLocale;
        }

        list($module, $locale, $filename) = $moduleLocale;

        if (file_exists($this->varPath.'overloads/'.$module.'/locales/'.$locale.'/'.$filename)) {
            return null;
        }

        if ($file->place == FilePlaceEnum::Var) {
            return $moduleLocale;
        }

        if (file_exists($this->varPath.'locales/'.$locale.'/'.$module.'/locales/'.$filename)) {
            return null;
        }

        if ($file->place == FilePlaceEnum::AppOverloads) {
            return $moduleLocale;
        }

        if (file_exists($this->appPath.'overloads/'.$module.'/locales/'.$locale.'/'.$filename)) {
            return null;
        }

        if ($file->place == FilePlaceEnum::App) {
            return $moduleLocale;
        }

        if (file_exists($this->appPath.'locales/'.$locale.'/'.$module.'/locales/'.$filename)) {
            return null;
        }

        if ($file->place == FilePlaceEnum::Module) {
            return $moduleLocale;
        }

        return null;
    }
}