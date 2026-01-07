<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2025-2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Template;

use Jelix\Core\AppInstance;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;

/**
 * Compile templates to be ready at runtime
 */
class TemplateWarmupCompiler
{
    protected $themeList = [];
    protected $localesList = [];

    /**
     * @var array [templatename][theme][locale] = file path
     */
    protected $allFileFlavors = [];

    /** @var CompiledTemplateInfo[]  */
    protected $templatesInfos = [];

    protected $compiledTemplates = [];

    /**
     * @var AppInstance
     */
    protected $app;

    public function __construct(AppInstance $app)
    {
        $this->app = $app;
    }

    protected function reset()
    {
        $this->themeList = [ 'default' => true ];
        // put the fallback locale at first element of the array
        if ($this->app->config->availableLocales[0] == $this->app->config->fallbackLocale) {
            $this->localesList = $this->app->config->availableLocales;
        }
        else {
            $this->localesList = array_diff($this->app->config->availableLocales, [ $this->app->config->fallbackLocale ]);
            array_unshift($this->localesList, $this->app->config->fallbackLocale);
        }
        $this->allFileFlavors = [];
        $this->compiledTemplates = [];
    }

    public function compileModule($module, $modulePath)
    {
        $this->reset();
        $this->readModuleTemplateFlavors($module, $modulePath);
        $this->consolidateAllFilesPath();
        $this->compileModuleTemplates();
    }

    protected function compileModuleTemplates($onlyTplPath = '')
    {
        $compiler = new TemplateCompiler();

        foreach($this->allFileFlavors as $tplName => $flavors) {
            foreach($flavors as $theme => $themeFlavors) {
                foreach($themeFlavors as $locale => $tplPath) {
                    if ($tplPath == '') {
                        continue;
                    }
                    if ($onlyTplPath != '' && $onlyTplPath != $tplPath) {
                        continue;
                    }

                    $tplInfos = $this->templatesInfos[$tplPath];
                    \jFile::createDir(dirname($tplInfos->compiledTemplatePath));

                    if ($tplInfos->theme != $theme || $tplInfos->locale != $locale) {
                        // the template for the given theme and locale is a template of another theme or locale
                         $tplInfos = new CompiledTemplateInfo(
                            $this->app->varLibPath,
                            $tplInfos->templatePath,
                            $tplInfos->module,
                            $tplInfos->templateFileName,
                            $theme,
                            $locale,
                            $tplInfos
                         );
                    }

                    if (!isset($this->compiledTemplates[$tplInfos->compiledTemplatePath])) {
                        $compiler->compileModuleCtplFile($tplInfos);
                        $this->compiledTemplates[$tplInfos->compiledTemplatePath] = true;
                    }
                }
            }
        }
    }

    /**
     * Retrieve all the template files of the given module from all directories containing templates.
     *
     * @param string $moduleName
     * @param string $modulePath
     * @param string $templateName the template name when we want to retrieve only files of a template
     */
    protected function readModuleTemplateFlavors($moduleName, $modulePath, $templateName='')
    {
        // retrieve list of files from module
        if (file_exists($modulePath.'/templates/')) {
            $this->readTemplatesList($modulePath.'/templates/', $moduleName, 'default', $templateName);
        }

        // retrieve list of files from module theme
        if (file_exists($modulePath.'/templates/themes')) {
            $dir = new \DirectoryIterator($modulePath.'/templates/themes');
            /** @var \SplFileInfo $fileinfo */
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                    $this->themeList[$fileinfo->getFilename()] = true;
                    $this->readTemplatesList($fileinfo->getPathname(), $moduleName, $fileinfo->getFilename(), $templateName);
                }
            }
        }

        // retrieve list of files from app/themes
        if (file_exists($this->app->appPath.'/app/themes/')) {
            $dir = new \DirectoryIterator($this->app->appPath.'/app/themes/');
            /** @var \SplFileInfo $fileinfo */
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                    $this->themeList[$fileinfo->getFilename()] = true;
                    if (file_exists($fileinfo->getPathname().'/'.$moduleName)) {
                        $this->readTemplatesList($fileinfo->getPathname().'/'.$moduleName, $moduleName,
                            $fileinfo->getFilename(), $templateName);
                    }
                }
            }
        }

        // retrieve list of files from var/themes
        if (file_exists($this->app->varPath.'/themes/')) {
            $dir = new \DirectoryIterator($this->app->varPath.'/themes/');
            /** @var \SplFileInfo $fileinfo */
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                    $this->themeList[$fileinfo->getFilename()] = true;
                    if (file_exists($fileinfo->getPathname().'/'.$moduleName)) {
                        $this->readTemplatesList($fileinfo->getPathname().'/'.$moduleName, $moduleName, $fileinfo->getFilename(), $templateName);
                    }
                }
            }
        }
    }

    /**
     * Generate a list of template from the given directory
     *
     * Expected content of the directory:
     * - `<locale>/<template>.ctpl`
     * - `<template>.ctpl`
     *
     * @param $directory
     * @return void
     */
    protected function readTemplatesList($directory, $moduleName, $theme, $template = '', $locale = '_')
    {
        $dir = new \DirectoryIterator($directory);
        /** @var \SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            if ($fileinfo->isDir()) {
                // if $locale is given, this is because we are inside a `<locale>` directory
                // so we ignore directories in this case, else we check if this is a `<locale>` directory.
                if ($locale == '_' && in_array($fileinfo->getFilename(), $this->localesList)) {
                    $this->readTemplatesList($fileinfo->getPathname(), $moduleName, $theme, $template, $fileinfo->getFilename());
                }
            }
            else if ($fileinfo->isFile() && $template) {
                if ($fileinfo->getFilename() == $template) {
                    $this->allFileFlavors[$template][$theme][$locale] = $fileinfo->getRealPath();
                    $this->templatesInfos[$fileinfo->getRealPath()] = new CompiledTemplateInfo($this->app->varLibPath, $fileinfo->getRealPath(), $moduleName, $template, $theme, $locale);
                }
            }
            else if ($fileinfo->isFile() && $fileinfo->getExtension() == 'ctpl') {
                $this->allFileFlavors[$fileinfo->getFilename()][$theme][$locale] = $fileinfo->getRealPath();
                $this->templatesInfos[$fileinfo->getRealPath()] = new CompiledTemplateInfo($this->app->varLibPath, $fileinfo->getRealPath(), $moduleName, $fileinfo->getFilename(), $theme, $locale);
            }
        }
    }

    /**
     * Determines an alternative file corresponding to the template when there is no explicit file
     * in a given theme and/or locale.
     *
     * @return void
     */
    protected function consolidateAllFilesPath()
    {
        $themeList = array_keys($this->themeList);
        $fallbackLocale = $this->app->config->fallbackLocale;
        foreach($this->allFileFlavors as $template => $flavors) {

            // we suppose that the default theme is the first one in the list
            foreach ($themeList as $theme) {
                if (!isset($flavors[$theme])) {
                    $flavors[$theme] = [];
                    if ($theme == 'default') {
                        $flavors[$theme] = array_fill_keys($this->localesList, '');
                    }
                    else {
                        $flavors[$theme] = $flavors['default'];
                        unset($flavors[$theme]['_']);
                    }
                    continue;
                }
                foreach($this->localesList as $locale) {
                    if (!isset($flavors[$theme][$locale])) {
                        if ($locale == $fallbackLocale) {
                            if (isset($flavors[$theme]['_'])) {
                                $flavors[$theme][$locale] = $flavors[$theme]['_'];
                            }
                            else if (isset($flavors['default'][$locale])) {
                                $flavors[$theme][$locale] = $flavors['default'][$locale];
                            }
                            else {
                                $flavors[$theme][$locale] = '';
                            }
                        } else {
                            $flavors[$theme][$locale] = $flavors[$theme][$fallbackLocale];
                        }
                    }
                }
            }
            $this->allFileFlavors[$template] = $flavors;
        }
    }

    public function compileSingleFile(FilePlace $file)
    {
        $moduleThemeLocale = $this->getModuleThemeAndLocale($file);
        if ($moduleThemeLocale === false) {
            return;
        }

        list($module, $theme, $locale, $tplName) = $moduleThemeLocale;
        // $locale == '_' means it is a template for any locales.
        if (!preg_match('/\.ctpl$/', $tplName)) {
            return;
        }

        $modulesList = $this->app->getEnabledModulesPaths();
        if (!isset($modulesList[$module])) {
            return;
        }

        $modulePath = $modulesList[$module];
        $this->reset();
        $this->readModuleTemplateFlavors($module, $modulePath, $tplName);

        $this->consolidateAllFilesPath();
        $this->compileModuleTemplates($file->filePath);
    }

    /**
     * Get the module name, the theme, the locale, and the filename corresponding
     * to the sub-path of the template
     *
     * @param FilePlace $file
     * @return array|false  [module, theme, locale, filename]
     */
    protected function getModuleThemeAndLocale(FilePlace $file)
    {
        $path = explode('/', $file->subPath);
        $nbItems = count($path);

        if ($file->place == FilePlaceEnum::Var ||
            $file->place == FilePlaceEnum::App
        ) {
            // [app|var]/themes/<theme>/<module>/<locale>/<tpl>.ctpl
            // [app|var]/themes/<theme>/<module>/<tpl>.ctpl
            if ($nbItems < 4 || $nbItems > 5 || $path[0] != 'themes') {
                return false;
            }
            if ($nbItems == 5) {
                return [$path[2], $path[1], $path[3], $path[4]];
            }
            return [$path[2], $path[1], '_', $path[3]];
        }
        else if ($file->place == FilePlaceEnum::Module) {
            // <module>/templates/themes/<theme>/<locale>/<tpl>.ctpl
            // <module>/templates/themes/<theme>/<tpl>.ctpl
            // <module>/templates/<locale>/<tpl>.ctpl
            // <module>/templates/<tpl>.ctpl
            if ($nbItems < 2 || $nbItems > 5 || $path[0] != 'templates') {
                return false;
            }
            if ($nbItems == 5) {
                if ($path[1] != 'themes') {
                    return false;
                }
                return [$file->module, $path[2], $path[3], $path[4]];
            }
            if ($nbItems == 4) {
                if ($path[1] != 'themes') {
                    return false;
                }
                return [$file->module, $path[2], '_', $path[3]];
            }
            if ($nbItems == 3) {
                return [$file->module, 'default', $path[1], $path[2]];
            }

            if ($nbItems == 2) {
                return [$file->module, 'default', '_', $path[1]];
            }
        }

        return false;
    }
}