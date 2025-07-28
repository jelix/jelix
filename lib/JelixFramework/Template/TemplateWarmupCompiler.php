<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2025 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Template;

use jAppInstance as AppInstance;

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

        foreach($this->allFileFlavors as $tplName => $flavors) {
            foreach($flavors as $theme => $themeFlavors) {
                foreach($themeFlavors as $locale => $tplPath) {
                    if ($tplPath == '') {
                        continue;
                    }
                    $compiledTemplatePath = $this->createCompiledTemplatePath($module, $theme, $locale, $tplName);
                    if (isset($this->compiledTemplates[$tplPath])) {
                        $compiledTemplatePathTarget = $this->compiledTemplates[$tplPath][0];
                        if (file_exists($compiledTemplatePath)) {
                            unlink($compiledTemplatePath);
                        }
                        symlink($compiledTemplatePathTarget, $compiledTemplatePath);
                    }
                    else {
                        if (file_exists($compiledTemplatePath) && is_link($compiledTemplatePath)) {
                            unlink($compiledTemplatePath);
                        }
                        $this->compileTemplate($module, $tplPath, $compiledTemplatePath);
                        $this->compiledTemplates[$tplPath] = [
                            $compiledTemplatePath
                        ];
                    }
                }
            }
        }
    }

    protected function readModuleTemplateFlavors($moduleName, $modulePath)
    {
        // retrieve list of files from module
        if (file_exists($modulePath.'/templates/')) {
            $this->readTemplatesList($modulePath.'/templates/', $moduleName, 'default');
        }

        // retrieve list of files from module theme
        if (file_exists($modulePath.'/templates/themes')) {
            $dir = new \DirectoryIterator($modulePath.'/templates/themes');
            /** @var \SplFileInfo $fileinfo */
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                    $this->themeList[$fileinfo->getFilename()] = true;
                    $this->readTemplatesList($fileinfo->getPathname(), $moduleName, $fileinfo->getFilename());
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
                        $this->readTemplatesList($fileinfo->getPathname().'/'.$moduleName, $moduleName, $fileinfo->getFilename());
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
                        $this->readTemplatesList($fileinfo->getPathname().'/'.$moduleName, $moduleName, $fileinfo->getFilename());
                    }
                }
            }
        }
    }

    /**
     * Generate a list of template from the given directory
     *
     * Expected content of the directory:
     * - `<locale>/<template>.tpl`
     * - `<template>.tpl`
     *
     * @param $directory
     * @return void
     */
    protected function readTemplatesList($directory, $moduleName, $theme, $locale = '_')
    {
        $dir = new \DirectoryIterator($directory);
        /** @var \SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            if ($fileinfo->isDir()) {
                if ($locale == '_' && in_array($fileinfo->getFilename(), $this->localesList)) {
                    $this->readTemplatesList($fileinfo->getPathname(), $moduleName, $theme, $fileinfo->getFilename());
                }
            }
            else if ($fileinfo->isFile() && $fileinfo->getExtension() == 'tpl') {
                $this->allFileFlavors[$fileinfo->getFilename()][$theme][$locale] = $fileinfo->getRealPath();
            }
        }
    }

    /**
     * check
     * @return void
     */
    protected function consolidateAllFilesPath()
    {
        $themeList = array_keys($this->themeList);
        $fallbackLocale = $this->app->config->fallbackLocale;
        foreach($this->allFileFlavors as $template => $flavors) {
            foreach ($themeList as $theme) {
                if (!isset($flavors[$theme])) {
                    $flavors[$theme] = [];
                    if ($theme == 'default') {
                        $flavors[$theme] = array_fill_keys($this->localesList, '');
                    }
                    else {
                        $flavors[$theme] = $flavors['default'];
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
                unset($flavors[$theme]['_']);
            }
            $this->allFileFlavors[$template] = $flavors;
        }
    }


    protected function createCompiledTemplatePath($module, $theme, $locale, $templateName)
    {
        return $this->app->varLibPath.'/Templates/'.ucfirst($module).'/'.ucfirst($theme).'/'.ucfirst(str_replace('_', '', $locale)).'/'.str_replace('.tpl', '.php', $templateName);
    }

    protected function compileTemplate($module, $sourceTemplateFile, $compiledTemplateFile)
    {
        $compiler = new \jTplCompiler();
        $compiler->compileModuleFile($module, $sourceTemplateFile, $compiledTemplateFile);
    }

}