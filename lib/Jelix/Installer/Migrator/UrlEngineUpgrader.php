<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016-2019 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Migrator;

class UrlEngineUpgrader
{
    /**
     * @var \Jelix\IniFile\IniModifierArray
     */
    protected $fullConfig;

    /**
     * @var \Jelix\IniFile\IniModifier
     */
    protected $mainConfig;

    /**
     * @var \Jelix\IniFile\IniModifier
     */
    protected $epConfig;

    protected $epId;

    /**
     * @var \Jelix\Routing\UrlMapping\XmlEntryPoint
     */
    protected $xmlMapEntryPoint;

    public function __construct(
        \Jelix\IniFile\IniModifierArray $fullConfig,
        $epId,
        \Jelix\Routing\UrlMapping\XmlEntryPoint $xml
    ) {
        $this->fullConfig = $fullConfig;
        $this->mainConfig = $fullConfig['main'];
        $this->epConfig = $fullConfig['entrypoint'];
        $this->xmlMapEntryPoint = $xml;
        $this->epId = $epId;
    }

    public function upgrade()
    {
        $engine = $this->fullConfig->getValue('engine', 'urlengine');

        switch ($engine) {
            case 'simple':
                $this->migrateSimple();

                break;

            case 'significant':
                $this->migrateSignificant();

                break;

            case 'basic_significant':
            default:
                $this->migrateBasicSignificant();
        }

        $defaultEntryPoint = $this->fullConfig->getValue('defaultEntrypoint', 'urlengine');
        if ($defaultEntryPoint == $this->epId) {
            $this->xmlMapEntryPoint->setOptions(array('default' => true));
        }

        $this->migrateStartModuleAction();

        self::cleanConfig($this->epConfig);
    }

    public static function cleanConfig(\Jelix\IniFile\IniModifier $ini)
    {
        $ini->removeValue('startModule', 0, null, false);
        $ini->removeValue('startAction', 0, null, false);
        $ini->removeValue('defaultEntrypoint', 'urlengine');
        $ini->removeValue('engine', 'urlengine');
        $ini->removeValue('simple_urlengine_https', 'urlengine');
        $ini->removeValue(null, 'simple_urlengine_entrypoints');
        $ini->removeValue(null, 'basic_significant_urlengine_entrypoints');
        $val = $ini->getValue('notfoundAct', 'urlengine');
        if ($val !== null) {
            $ini->removeValue('notfoundAct', 'urlengine');
            $ini->setValue('notFoundAct', $val, 'urlengine');
        }
    }

    protected $httpsSelectors;

    protected function migrateSimple($isBasicSignificantUrl = false)
    {
        $https = preg_split('/[\\s,]+/', $this->fullConfig->getValue('simple_urlengine_https', 'urlengine'));
        $this->httpsSelectors = array_combine($https, array_fill(0, count($https), true));

        $entrypoints = $this->fullConfig->getValues('simple_urlengine_entrypoints');
        foreach ($entrypoints as $entrypoint => $selectors) {
            $entrypoint = str_replace('.php', '', $entrypoint);
            if ($entrypoint == $this->epId) {
                $selectors = preg_split('/[\\s,]+/', $selectors);
                foreach ($selectors as $sel2) {
                    $this->storeUrl($sel2, $isBasicSignificantUrl);
                }

                break;
            }
        }
    }

    protected function migrateBasicSignificant()
    {
        $this->migrateSimple(true);
        if ($this->fullConfig->isSection('basic_significant_urlengine_entrypoints')) {
            // read basic_significant_urlengine_entrypoints
            // if the entry point is not in this section, or value is off
            // add an attribute noentrypoint=true
            $addEntryPoints = $this->fullConfig->getValues('basic_significant_urlengine_entrypoints');
            if (!isset($addEntryPoints[$this->epId])
                || !$addEntryPoints[$this->epId]) {
                $this->xmlMapEntryPoint->setOptions(array('noentrypoint' => true));
            }
        }
    }

    protected function migrateSignificant()
    {
        // doing something ?
        // remove startModule ?
        // replace <*entrypoint> ?
    }

    protected function migrateStartModuleAction()
    {
        $startModule = $this->fullConfig->getValue('startModule');
        $startAction = $this->fullConfig->getValue('startAction');
        if ($startModule != $this->mainConfig->getValue('startModule')
            || $startAction != $this->mainConfig->getValue('startAction')) {
            $this->xmlMapEntryPoint->addUrlAction('/', $startModule, $startAction, null, null, array('default' => true));
            $this->xmlMapEntryPoint->addUrlModule('', $startModule);
        }
    }

    protected function storeUrl($selStr, $isBasicSignificantUrl)
    {
        $https = false;
        $options = null;

        if ($isBasicSignificantUrl) {
            $aliases = $this->fullConfig->getValues('basic_significant_urlengine_aliases');
            if ($aliases) {
                $moduleAliases = array_flip($aliases);
            } else {
                $moduleAliases = array();
            }
        }

        if (preg_match('/^@([a-zA-Z0-9_]+)$/', $selStr, $m)) {
            $requestType = $m[1];
            $https = isset($this->httpsSelectors[$selStr]);
            $this->xmlMapEntryPoint->setOptions(array('https' => $https, 'default' => true));
        } elseif (preg_match('/^([a-zA-Z0-9_\\.]+)~([a-zA-Z0-9_:]+)@([a-zA-Z0-9_]+)$/', $selStr, $m)) {
            // --> <url pathinfo="/$module/$controller/$method" module="$module" action="$action"/>
            $module = $m[1];
            $action = $m[2];
            if (strpos($action, ':') !== false) {
                list($ctrl, $method) = explode(':', $action);
            } else {
                $ctrl = 'default';
                $method = $action;
                $action = 'default:'.$action;
            }

            $requestType = $m[3];

            if (isset($this->httpsSelectors[$module.'~'.$action.'@'.$requestType])) {
                $https = true;
            } elseif (isset($this->httpsSelectors[$module.'~*@'.$requestType])) {
                $https = true;
            } elseif (isset($this->httpsSelectors['@'.$requestType])) {
                $https = true;
            }

            if ($https) {
                $options = array('https' => true);
            }

            if ($isBasicSignificantUrl && isset($moduleAliases[$module])) {
                $pathinfo = '/'.$moduleAliases[$module].'/'.$ctrl.'/'.$method;
            } else {
                $pathinfo = '/'.$module.'/'.$ctrl.'/'.$method;
            }

            $this->xmlMapEntryPoint->addUrlAction($pathinfo, $module, $action, null, null, $options);
        } elseif (preg_match('/^([a-zA-Z0-9_\\.]+)~([a-zA-Z0-9_]+):\\*@([a-zA-Z0-9_]+)$/', $selStr, $m)) {
            // --> <url pathinfo="/module/controller" module="$module" controller="$controller"/>
            $module = $m[1];
            $ctrl = $m[2];
            $requestType = $m[3];

            if (isset($this->httpsSelectors[$module.'~'.$ctrl.':*@'.$requestType])) {
                $https = true;
            } elseif (isset($this->httpsSelectors[$module.'~*@'.$requestType])) {
                $https = true;
            } elseif (isset($this->httpsSelectors['@'.$requestType])) {
                $https = true;
            }

            if ($https) {
                $options = array('https' => true);
            }

            if ($isBasicSignificantUrl && isset($moduleAliases[$module])) {
                $pathinfo = '/'.$moduleAliases[$module].'/'.$ctrl;
            } else {
                $pathinfo = '/'.$module.'/'.$ctrl;
            }

            $this->xmlMapEntryPoint->addUrlController($pathinfo, $module, $ctrl, $options);
        } elseif (preg_match('/^([a-zA-Z0-9_\\.]+)~\\*@([a-zA-Z0-9_]+)$/', $selStr, $m)) {
            // --> <url module=""/>
            $module = $m[1];
            $requestType = $m[2];
            if (isset($this->httpsSelectors[$module.'~*@'.$requestType])) {
                $https = true;
            } elseif (isset($this->httpsSelectors['@'.$requestType])) {
                $https = true;
            }

            if ($https) {
                $options = array('https' => true);
            }
            if ($isBasicSignificantUrl && isset($moduleAliases[$module])) {
                $pathinfo = '/'.$moduleAliases[$module];
            } else {
                $pathinfo = '';
            }

            $this->xmlMapEntryPoint->addUrlModule($pathinfo, $module, $options);
        }
    }
}
