<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\DaoUtils;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;

class DaoWarmupCompiler
{

    protected $appPath;
    protected $varPath;
    protected $buildPath;

    protected $compiledFiles = [];

    function __construct($appPath, $varPath, $buildPath)
    {
        $this->appPath = $appPath;
        $this->varPath = $varPath;
        $this->buildPath = $buildPath;
    }

    /**
     * Compile all daos of the given module.
     *
     * To be called during the installation of the application for example.
     *
     * Daos are searched into:
     * - var/overloads/ (highest priority)
     * - app/overloads/
     * - daos/ into the original module directory (lowest priority)
     *
     * @param string $module module name
     * @param string $modulePath full path to the module
     * @param array list of sql langage to which compilation should done
     * @return void
     */
    public function compileModule($module, $modulePath, array $sqlTypeList)
    {
        // /!\ Warning, the order is important
        // the daos are tried to be found from the directory having the highest priority
        // to the directory having the lower priority.

        // check if the dao has been overloaded in var/
        $overloadedPath = $this->varPath.'overloads/'.$module.'/daos/';
        if (is_readable($overloadedPath)) {
            $this->compileFromDirectory($module, $overloadedPath, $sqlTypeList);
        }

        // check if the dao has been overloaded in app/
        $overloadedPath = $this->appPath.'app/overloads/'.$module.'/daos/';
        if (is_readable($overloadedPath)) {
            $this->compileFromDirectory($module, $overloadedPath, $sqlTypeList);
        }

        if (is_readable($modulePath.'daos/')) {
            $this->compileFromDirectory($module, $modulePath.'daos/', $sqlTypeList);
        }

    }


    /**
     * @param string $module module name
     * @param string $directory the path where some forms of the module can be found
     * @return void
     */
    protected function compileFromDirectory($module, $directory, array $sqlTypeList)
    {

        \jApp::pushCurrentModule($module);
        $dir = new \DirectoryIterator($directory);
        /** @var \SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            $file = $fileinfo->getFilename();
            if (str_ends_with($file, '.dao.xml')) {

                $key = $module.'/'.$file;
                if (isset($this->compiledFiles[$key])) {
                    // if the file is already compiled, this is because it has been found into a directory
                    // that have a higher priority. So we don't recompile it.
                    // We don't check the cache file, because we don't want to have to delete all cache files
                    // before compiling all form files.
                    continue;
                }
                $this->compiledFiles[$key] = true;

                $this->compileFile($module, $fileinfo->getPathname(), $file, $sqlTypeList);
            }
        }
        \jApp::popCurrentModule();
    }


    public function compileSingleFile(FilePlace $file, array $sqlTypeList)
    {
        if ($this->isPriorityPath($file)) {
            $this->compileFile($file->module, $file->filePath, str_replace('forms/', '', $file->subPath), $sqlTypeList);
        }
    }

    /**
     * @param FilePlace $file
     * @return boolean
     */
    protected function isPriorityPath(FilePlace $file)
    {

        if ($file->place == FilePlaceEnum::VarOverloads) {
            return true;
        }

        $module = $file->module;

        // the file is not into var/overloads, but if there is a copy in
        // var/overloads, so we won't compile the file
        if (file_exists($this->varPath.'overloads/'.$module.'/'.$file->subPath)) {
            return false;
        }

        if ($file->place == FilePlaceEnum::AppOverloads) {
            return true;
        }

        // the file is not into app/overloads, but if there is a copy in
        // app/overloads, so we won't compile the file
        if (file_exists($this->appPath.'overloads/'.$module.'/'.$file->subPath)) {
            return false;
        }

        if ($file->place == FilePlaceEnum::Module) {
            return true;
        }

        return false;
    }

    /**
     * Gives the path of the file that is the result of the compilation of a dao file.
     *
     * @param string $module
     * @param string $fileName the file name (not the path)
     * @param string $buildPath the main build directory where compiled files are stored.
     * @return string the full path of the compiled file.
     */
    public static function getCacheFileName($module, $fileName, $buildPath)
    {
        return $buildPath.'Daos/'.ucfirst($module).'/'.ucfirst(str_replace('.dao.xml', '', $fileName)).'.php';
    }

    protected function getCachePath($module, $fileName)
    {
        $cachePath = self::getCacheFileName($module, $fileName, $this->buildPath);
        \jFile::createDir(dirname($cachePath));
        return $cachePath;
    }

    /**
     * @param string $module
     * @param string $sourceFile
     * @param string $sourceFileName
     * @return true
     * @throws \jException
     */
    protected function compileFile($module, $sourceFile, $sourceFileName, $sqlTypeList)
    {
        $daoName = str_replace('.dao.xml', '', $sourceFileName);

        foreach ($sqlTypeList as $sqlType) {

            $daoFile = new DaoModuleFile(
                $module,
                $daoName,
                $sourceFile,
                $sqlType,
                $this->buildPath
            );
            $context = new DaoContext($sqlType);
            $compiler = new Compiler();
            $compiler->compile($daoFile, $context);
        }

        return true;
    }

}