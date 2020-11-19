<?php
/**
 * @package     jelix
 * @subpackage  core-module
 *
 * @author      Laurent Jouanneau
 * @copyright   2017-2019 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Migrator;

class WebAssetsUpgrader
{
    /**
     * @var \Jelix\IniFile\IniReaderInterface
     */
    protected $refConfig;

    /**
     * WebAssetsUpgrader constructor.
     *
     * @param \Jelix\IniFile\IniReaderInterface $refConfig the config containing default values
     * @param $epId
     * @param \Jelix\Routing\UrlMapping\XmlEntryPoint $xml
     */
    public function __construct(\Jelix\IniFile\IniReaderInterface $refConfig)
    {
        $this->refConfig = $refConfig;
    }

    /**
     * @param \Jelix\IniFile\IniModifierArray $config       The configuration in which we found actual values
     * @param \Jelix\IniFile\IniModifier      $targetConfig the file to modify
     */
    public function changeConfig(
        \Jelix\IniFile\IniModifierArray $config,
        \Jelix\IniFile\IniModifier $targetConfig
    ) {
        $defaultConfig = $config['default'];

        $jqueryPath = $config->getValue('jqueryPath', 'urlengine');
        $jqueryPathOrig = $this->refConfig->getValue('jqueryPath', 'urlengine');
        $jqueryPathPattern = '$jelix/jquery';
        if ($jqueryPath && $jqueryPathOrig != $jqueryPath) {
            $jqueryPathPattern = $jqueryPath;
        }

        // move jquery to webassets
        $jqueryJs = $config->getValue('jquery', 'jquery');
        if ($jqueryJs && $jqueryJs != '$jqueryPath/jquery.js') {
            $targetConfig->setValue('useCollection', 'main', 'webassets');
            $targetConfig->setValue('jquery.js', str_replace('$jqueryPath', $jqueryPathPattern, $jqueryJs), 'webassets_main');
        }

        $jqueryJs = $config->getValue('jqueryui.js', 'jquery');
        if ($jqueryJs &&
            $jqueryJs != array('$jqueryPath/ui/jquery-ui-core-widg-mous-posi.custom.min.js') &&
            $jqueryJs != '$jqueryPath/ui/jquery-ui-core-widg-mous-posi.custom.min.js'
        ) {
            $targetConfig->setValue('useCollection', 'main', 'webassets');
            $targetConfig->setValue('jqueryui.js', str_replace('$jqueryPath', $jqueryPathPattern, $jqueryJs), 'webassets_main');
        }

        $jqueryCss = $config->getValue('jqueryui.css', 'jquery');
        if ($jqueryCss &&
            $jqueryCss != array('$jqueryPath/themes/base/jquery.ui.all.css') &&
            $jqueryCss != '$jqueryPath/themes/base/jquery.ui.all.css'
        ) {
            $targetConfig->setValue('useCollection', 'main', 'webassets');
            $targetConfig->setValue('jqueryui.css', str_replace('$jqueryPath', $jqueryPathPattern, $jqueryCss), 'webassets_main');
        }

        $targetConfig->removeSection('jquery');

        // move datepickers scripts to webassets

        $datapickers = $config->getValues('datepickers');
        if ($datapickers) {
            foreach ($datapickers as $configName => $script) {
                if (strpos($configName, '.') !== false) {
                    continue;
                }
                $js = $config->getValue($configName.'.js', 'datepickers');
                if ($js) {
                    if (is_array($js)) {
                        array_unshift($js, $script);
                    } else {
                        $js = array($script, $js);
                    }
                    $js = array_map(function ($src) use ($jqueryPathPattern) {
                        return str_replace('$jqueryPath', $jqueryPathPattern, $src);
                    }, $js);
                    $targetConfig->setValue('jforms_datepicker_'.$configName.'.js', $js, 'webassets_main');
                }
                $css = $config->getValue($configName.'.css', 'datepickers');
                if ($css) {
                    if (is_array($css)) {
                        array_unshift($css, $script);
                    } else {
                        $css = array($script, $css);
                    }
                    $css = array_map(function ($src) use ($jqueryPathPattern) {
                        return str_replace('$jqueryPath', $jqueryPathPattern, $src);
                    }, $css);
                    $targetConfig->setValue('jforms_datepicker_'.$configName.'.css', $css, 'webassets_main');
                }
                $targetConfig->setValue('jforms_datepicker_'.$configName.'.require', 'jquery', 'webassets_main');
            }
            $targetConfig->removeSection('datepickers');
        }

        $datapickers = $config->getValues('datetimepickers');
        if ($datapickers) {
            foreach ($datapickers as $configName => $script) {
                if (strpos($configName, '.') !== false) {
                    continue;
                }
                $js = $config->getValue($configName.'.js', 'datetimepickers');
                if ($js) {
                    if (is_array($js)) {
                        array_unshift($js, $script);
                    } else {
                        $js = array($script, $js);
                    }
                    $js = array_map(function ($src) use ($jqueryPathPattern) {
                        return str_replace('$jqueryPath', $jqueryPathPattern, $src);
                    }, $js);
                    $targetConfig->setValue('jforms_datetimepicker_'.$configName.'.js', $js, 'webassets_main');
                }
                $css = $config->getValue($configName.'.css', 'datetimepickers');
                if ($css) {
                    if (is_array($css)) {
                        array_unshift($css, $script);
                    } else {
                        $css = array($script, $css);
                    }
                    $css = array_map(function ($src) use ($jqueryPathPattern) {
                        return str_replace('$jqueryPath', $jqueryPathPattern, $src);
                    }, $css);
                    $targetConfig->setValue('jforms_datetimepicker_'.$configName.'.css', $css, 'webassets_main');
                }
                $targetConfig->setValue('jforms_datetimepicker_'.$configName.'.require', 'jquery', 'webassets_main');
            }
            $targetConfig->removeSection('datetimepickers');
        }

        // move htmleditor assets
        $htmleditorconfs = $targetConfig->getValues('htmleditors');
        if ($htmleditorconfs) {
            $newWebAssets = array();
            foreach ($htmleditorconfs as $name => $val) {
                list($configName, $typeConfig) = explode('.', $name, 2);
                if ($typeConfig == 'engine.name') {
                    continue;
                }
                if (isset($newWebAssets[$configName])) {
                    if (strpos($typeConfig, 'skin.') === 0) {
                        $skin = substr($typeConfig, strlen('skin.'));
                        $newWebAssets[$configName]['skin'][$skin] = $config->getValue($name, 'htmleditors');
                        $targetConfig->removeValue($name, 'htmleditors');
                    }

                    continue;
                }

                $newWebAssets[$configName] = array(
                    'js' => array(),
                    'skin' => array(),
                );
                $val = $config->getValue($configName.'.engine.file', 'htmleditors');
                if ($val) {
                    if (!is_array($val)) {
                        $val = array($val);
                    }
                    $newWebAssets[$configName]['js'] = array_merge(
                        $newWebAssets[$configName]['js'],
                        $val
                    );
                    $targetConfig->removeValue($configName.'.engine.file', 'htmleditors');
                }
                $val = $config->getValue($configName.'.default', 'htmleditors');
                if ($val) {
                    if (!is_array($val)) {
                        $val = array($val);
                    }
                    $newWebAssets[$configName]['js'] = array_merge(
                        $newWebAssets[$configName]['js'],
                        $val
                    );
                    $targetConfig->removeValue($configName.'.default', 'htmleditors');
                }
                if (strpos($typeConfig, 'skin.') === 0) {
                    $skin = substr($typeConfig, strlen('skin.'));
                    $newWebAssets[$configName]['skin'][$skin] = $config->getValue($name, 'htmleditors');
                    $targetConfig->removeValue($name, 'htmleditors');
                }
            }

            if (count($newWebAssets)) {
                $config->setValue('useCollection', 'main', 'webassets');
                foreach ($newWebAssets as $configName => $assets) {
                    $assets['js'] = array_map(function ($src) use ($jqueryPathPattern) {
                        return str_replace('$jqueryPath', $jqueryPathPattern, $src);
                    }, $assets['js']);

                    $targetConfig->setValue('jforms_htmleditor_'.$configName.'.js', $assets['js'], 'webassets_main');
                    $targetConfig->setValue('jforms_htmleditor_'.$configName.'.require', '', 'webassets_main');
                    foreach ($assets['skin'] as $skin => $skassets) {
                        if (is_array($skassets)) {
                            $skassets = array_map(function ($src) use ($jqueryPathPattern) {
                                return str_replace('$jqueryPath', $jqueryPathPattern, $src);
                            }, $skassets);
                        } else {
                            $skassets = str_replace('$jqueryPath', $jqueryPathPattern, $skassets);
                        }
                        $targetConfig->setValue('jforms_htmleditor_'.$configName.'.skin.'.$skin, $skassets, 'webassets_main');
                    }
                }
            }
        }

        // move wikieditor assets
        $wikieditorconfs = $targetConfig->getValues('wikieditors');
        if ($wikieditorconfs) {
            $newWebAssets = array();
            foreach ($wikieditorconfs as $name => $val) {
                list($configName, $typeConfig) = explode('.', $name, 2);
                if ($typeConfig == 'engine.name' || $typeConfig == 'wiki.rules') {
                    continue;
                }
                if ($typeConfig == 'config.path' || $typeConfig == 'image.path') {
                    $targetConfig->removeValue($name, 'wikieditors');

                    continue;
                }
                if (isset($newWebAssets[$configName])) {
                    continue;
                }

                $newWebAssets[$configName] = array(
                    'js' => array(),
                    'css' => array(),
                );
                $val = $config->getValue($configName.'.engine.file', 'wikieditors');
                if ($val) {
                    if (!is_array($val)) {
                        $val = array($val);
                    }
                    $newWebAssets[$configName]['js'] = array_merge(
                        $newWebAssets[$configName]['js'],
                        $val
                    );
                    $targetConfig->removeValue($configName.'.engine.file', 'wikieditors');
                }
                $val = $config->getValue($configName.'.skin', 'wikieditors');
                if ($val) {
                    if (!is_array($val)) {
                        $val = array($val);
                    }
                    $newWebAssets[$configName]['css'] = array_merge(
                        $newWebAssets[$configName]['css'],
                        $val
                    );
                    $targetConfig->removeValue($configName.'.skin', 'wikieditors');
                }
            }

            if (count($newWebAssets)) {
                $config->setValue('useCollection', 'main', 'webassets');
                foreach ($newWebAssets as $configName => $assets) {
                    $assets['js'] = array_map(function ($src) use ($jqueryPathPattern) {
                        return str_replace('$jqueryPath', $jqueryPathPattern, $src);
                    }, $assets['js']);
                    $assets['css'] = array_map(function ($src) use ($jqueryPathPattern) {
                        return str_replace('$jqueryPath', $jqueryPathPattern, $src);
                    }, $assets['css']);
                    $targetConfig->setValue('jforms_wikieditor_'.$configName.'.js', $assets['js'], 'webassets_main');
                    $targetConfig->setValue('jforms_wikieditor_'.$configName.'.css', $assets['css'], 'webassets_main');
                    $targetConfig->setValue('jforms_wikieditor_'.$configName.'.require', '', 'webassets_main');
                }
            }
        }
    }
}
