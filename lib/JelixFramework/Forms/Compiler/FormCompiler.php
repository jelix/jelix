<?php
/**
 * @author     Laurent Jouanneau
 * @contributor Loic Mathaud, Dominique Papin
 * @contributor Uriel Corfa (Emotic SARL), Julien Issler
 *
 * @copyright   2006-2025 Laurent Jouanneau
 * @copyright   2007 Loic Mathaud, 2007 Dominique Papin
 * @copyright   2007 Emotic SARL
 * @copyright   2008 Julien Issler
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Forms\Compiler;

use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;

/**
 * Generates form class from an xml file describing the form.
 *
 */
class FormCompiler
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
     * Compile all forms of the given module.
     *
     * To be called during the installation of the application for example.
     *
     * Forms are searched into:
     * - var/overloads/ (highest priority)
     * - app/overloads/
     * - forms/ into the original module directory (lowest priority)
     *
     * @param string $module module name
     * @param string $modulePath full path to the module
     * @return void
     */
    public function compileModule($module, $modulePath)
    {
        // /!\ Warning, the order is important
        // the forms are tried to be found from the directory having the highest priority
        // to the directory having the lower priority.

        // check if the form has been overloaded in var/
        $overloadedPath = $this->varPath.'overloads/'.$module.'/forms/';
        if (is_readable($overloadedPath)) {
            $this->compileFromDirectory($module, $overloadedPath);
        }

        // check if the form has been overloaded in app/
        $overloadedPath = $this->appPath.'app/overloads/'.$module.'/forms/';
        if (is_readable($overloadedPath)) {
            $this->compileFromDirectory($module, $overloadedPath);
        }

        if (is_readable($modulePath.'forms/')) {
            $this->compileFromDirectory($module, $modulePath.'forms/');
        }

    }

    /**
     * @param string $module module name
     * @param string $directory the path where some forms of the module can be found
     * @return void
     */
    protected function compileFromDirectory($module, $directory)
    {
        $dir = new \DirectoryIterator($directory);
        /** @var \SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            $file = $fileinfo->getFilename();
            if (str_ends_with($file, '.form.xml')) {

                $key = $module.'/'.$file;
                if (isset($this->compiledFiles[$key])) {
                    // if the file is already compiled, this is because it has been found into a directory
                    // that have a higher priority. So we don't recompile it.
                    // We don't check the cache file, because we don't want to have to delete all cache files
                    // before compiling all form files.
                    continue;
                }
                $this->compiledFiles[$key] = true;

                $this->compileFile($module, $fileinfo->getPathname(), $file);
            }
        }
    }

    public function compileSingleFile(FilePlace $file)
    {
        if ($this->isPriorityPath($file)) {
            $this->compileFile($file->module, $file->filePath, str_replace('forms/', '', $file->subPath));
        }
    }

    /**
     * Gives the path of the file that is the result of the compilation of a form file.
     *
     * @param string $module
     * @param string $fileName the file name (not the path)
     * @param string $buildPath the main build directory where compiled files are stored.
     * @return string the full path of the compiled file.
     */
    public static function getCacheFileName($module, $fileName, $buildPath)
    {
        return $buildPath.'Forms/'.ucfirst($module).'/'.ucfirst(str_replace('.form.xml', '', $fileName)).'.php';
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
    protected function compileFile($module, $sourceFile, $sourceFileName)
    {
        $cachePath = $this->getCachePath($module, $sourceFileName);

        // load XML file
        $doc = new \DOMDocument();

        if (!$doc->load($sourceFile)) {
            throw new \jException('jelix~formserr.invalid.xml.file', array($sourceFile));
        }

        if ($doc->documentElement->namespaceURI == XmlCompiler10::NS) {
            $compiler = new XmlCompiler10($sourceFile);
        } elseif ($doc->documentElement->namespaceURI == XmlCompiler11::NS) {
            if ($doc->documentElement->localName != 'form') {
                throw new \jException('jelix~formserr.bad.root.tag', array($doc->documentElement->localName, $sourceFile));
            }

            $compiler = new XmlCompiler11($sourceFile);
        } else {
            throw new \jException('jelix~formserr.namespace.wrong', array($sourceFile));
        }

        $source = array();
        $source[] = "<?php \n";

        $source[] = 'namespace Jelix\\BuiltComponents\\Forms\\' . ucfirst($module) . ';';
        $source[] = 'use \\Jelix\\Forms\\Controls as Ctl;';
        $source[] = 'use \\Jelix\\Locale\\Locale;';
        $source[] = 'class ' . ucfirst(str_replace('.form.xml', '', $sourceFileName)) . ' extends \\Jelix\\Forms\\FormInstance {';

        $source[] = ' public function __construct($sel, &$container, $reset = false){';
        $source[] = '          parent::__construct($sel, $container, $reset);';

        $compiler->compile($doc, $source);

        $source[] = "  }\n}\n";
        \jFile::write($cachePath, implode("\n", $source));

        return true;
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
}
