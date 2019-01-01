<?php
/**
 * @package     jelix
 * @subpackage  core-module
 * @author      Laurent Jouanneau
 * @copyright   2017-2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

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
    function __construct(\Jelix\IniFile\IniReaderInterface $refConfig)
    {
        $this->refConfig = $refConfig;
    }

    /**
     * @param \Jelix\IniFile\IniModifierArray $config The configuration in which we found actual values
     * @param \Jelix\IniFile\IniModifier $targetConfig the file to modify
     */
    public function changeConfig(\Jelix\IniFile\IniModifierArray $config,
                                 \Jelix\IniFile\IniModifier $targetConfig) {
        $defaultConfig = $config['default'];

        // move jqueryPath to webassets
        $jqueryPath = $config->getValue('jqueryPath', 'urlengine');
        $jqueryPathOrig = $this->refConfig->getValue('jqueryPath', 'urlengine');
        if ($jqueryPathOrig != $jqueryPath &&
            $targetConfig->getValue('jquery.js', 'webassets_common') === null) {
            $targetConfig->setValue('useCollection', 'main', 'webassets');
            $targetConfig->setValue('jquery.js', $jqueryPath, 'webassets_main');
        }

        // move datepickers scripts to webassets

        $defaultDatepickerCss = $defaultConfig->getValue('jforms_datepicker_default.css', 'webassets_common');
        $defaultDatepickerJs = $defaultConfig->getValue('jforms_datepicker_default.js', 'webassets_common');
        $defaultDatepickerRequire = $defaultConfig->getValue('jforms_datepicker_default.require', 'webassets_common');
        $datapickers = $targetConfig->getValues('datepickers');
        if ($datapickers) {
            foreach($datapickers as $configName => $script) {
                if ($configName == 'default' &&
                    $script == 'jelix/js/jforms/datepickers/default/init.js') {
                    $targetConfig->removeValue($configName, 'datepickers');
                    continue;
                }
                $config->setValue('useCollection', 'main', 'webassets');
                if ($script == 'jelix/js/jforms/datepickers/default/init.js') {
                    $targetConfig->setValue('jforms_datepicker_'.$configName.'.css', $defaultDatepickerCss, 'webassets_main');
                    $targetConfig->setValue('jforms_datepicker_'.$configName.'.js', $defaultDatepickerJs, 'webassets_main');
                    $targetConfig->setValue('jforms_datepicker_'.$configName.'.require', $defaultDatepickerRequire, 'webassets_main');
                }
                else {
                    $targetConfig->setValue('jforms_datepicker_'.$configName.'.js', $script, 'webassets_main');
                }
                $targetConfig->removeValue($configName, 'datepickers');
            }
        }

        // move htmleditor assets
        $htmleditorconfs = $targetConfig->getValues('htmleditors');
        if ($htmleditorconfs) {
            $newWebAssets = array();
            foreach ($htmleditorconfs as $name=>$val) {
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
                    'js'=>array(),
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
                foreach($newWebAssets as $configName=>$assets) {

                    $targetConfig->setValue('jforms_htmleditor_'.$configName.'.js', $assets['js'], 'webassets_main');
                    $targetConfig->setValue('jforms_htmleditor_'.$configName.'.require', '', 'webassets_main');
                    foreach($assets['skin'] as $skin => $skassets) {
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
                $val = $config->getValue($configName . '.engine.file', 'wikieditors');
                if ($val) {
                    if (!is_array($val)) {
                        $val = array($val);
                    }
                    $newWebAssets[$configName]['js'] = array_merge(
                        $newWebAssets[$configName]['js'],
                        $val
                    );
                    $targetConfig->removeValue($configName . '.engine.file', 'wikieditors');
                }
                $val = $config->getValue($configName . '.skin', 'wikieditors');
                if ($val) {
                    if (!is_array($val)) {
                        $val = array($val);
                    }
                    $newWebAssets[$configName]['css'] = array_merge(
                        $newWebAssets[$configName]['css'],
                        $val
                    );
                    $targetConfig->removeValue($configName . '.skin', 'wikieditors');
                }
            }

            if (count($newWebAssets)) {
                $config->setValue('useCollection', 'main', 'webassets');
                foreach ($newWebAssets as $configName => $assets) {
                    $targetConfig->setValue('jforms_wikieditor_' . $configName . '.js', $assets['js'], 'webassets_main');
                    $targetConfig->setValue('jforms_wikieditor_' . $configName . '.css', $assets['css'], 'webassets_main');
                    $targetConfig->setValue('jforms_wikieditor_' . $configName . '.require', '', 'webassets_main');
                }
            }
        }
    }
}
