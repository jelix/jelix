<?php
/**
 * @package      jelix
 * @subpackage   core
 * @author       Laurent Jouanneau
 * @copyright    2017 Laurent Jouanneau
 * @link         http://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


class webassetsConfigCompilerPlugin implements \Jelix\Core\Config\CompilerPluginInterface {

    function getPriority() {
        return 18;
    }

    function atStart($config) {

        $vars = get_object_vars($config);
        if (isset($vars['webassets_common'])) {
            $this->parseAssetsSet($config, 'webassets_common', true);
        }
        foreach($vars as $section=>$props) {
            if (strpos($section, 'webassets_') !== 0 || $section == 'webassets_common') {
                continue;
            }
            $this->parseAssetsSet($config, $section);
        }
    }

    function onModule($config, \Jelix\Core\Infos\ModuleInfos $module) {

    }

    function atEnd($config) {

    }

    protected $commonAssetsGroups = array();

    protected function parseAssetsSet($config, $sectionName, $isCommon = false) {

        // read all assets groups
        $assetsGroups = array();
        foreach($config->$sectionName as $prop => $val) {
            if (!preg_match('/^(.+)\\.([a-z]+)$/', $prop, $m)) {
                continue;
            }
            $groupName = $m[1];
            $property = $m[2];
            if (!isset($assetsGroups[$groupName])) {
                $assetsGroups[$groupName] = array(
                    'css'=>array(),
                    'js'=>array(),
                    'include'=>array(),
                    'require'=>array(),
                );
            }

            $values = array();
            if ($property == 'css' || $property == 'js') {

                if (!is_array($val)) {
                    $val = array($val);
                }

                foreach($val as $assetItem) {
                    $list = preg_split('/ *, */', $assetItem);
                    foreach($list as $asset) {
                        if ($asset != '') {
                            $values[] = $this->parseAsset($config, $asset);
                        }
                    }
                }
            }
            else if ($property == 'include' || $property == 'require') {
                if (!is_array($val)) {
                    $val = array($val);
                }
                foreach($val as $depGroupName) {
                    $values = array_merge($values, preg_split('/ *, */', $depGroupName));
                }
                $values = array_unique($values);
            }

            $assetsGroups[$groupName][$property] = $values;
        }

        if (!$isCommon) {
            // backport all common assets groups that are not already redefined
            // into the current assets set.
            foreach($this->commonAssetsGroups as $groupName => $properties) {
                if (!isset($assetsGroups[$groupName])) {
                    $assetsGroups[$groupName] = $properties;
                }
            }
        }

        foreach($assetsGroups as $groupName => $properties) {
            foreach($properties as $name => $val) {
                if ($name == 'include' || $name == 'require') {
                    $val = array_filter($val, function($depGroupName) use ($assetsGroups) {
                        return isset($assetsGroups[$depGroupName]);
                    });
                }
                $config->{$sectionName}[$groupName.'.'.$name] = $val;
            }
        }
        if ($isCommon) {
            $this->commonAssetsGroups = $assetsGroups;
        }
    }


    protected function parseAsset($config, $asset) {
        if ($asset[0] == '/' || preg_match('!^https?://!', $asset)) {
            if (strpos($asset, '$lang') !== false || strpos($asset, '$locale') !== false) {
                return 'l>'.$asset;
            }
            return 'u>'.$asset;
        }
        else if (preg_match("/^([a-zA-Z0-9_\\.]+)~([a-zA-Z0-9_:]+)(?:@([a-zA-Z0-9_]+))?$/", $asset)) {
            return 'a>'.$asset;
        }
        else if (preg_match('/^([a-zA-Z0-9_\\.]+):/', $asset)) {
            return 'm>'.$asset;
        }
        else if (preg_match('!^\\$theme/(.+)!', $asset, $m)) {
            if (strpos($asset, '$lang') !== false || strpos($asset, '$locale') !== false) {
                return 's>'.$m[1];
            }
            return 't>'.$m[1];
        }
        else if (preg_match('!^\\$jelix/(.+)!', $asset, $m)) {
            if (strpos($asset, '$lang') !== false || strpos($asset, '$locale') !== false) {
                return 'l>'.$config->urlengine['jelixWWWPath'].$m[1];
            }
            return 'u>'.$config->urlengine['jelixWWWPath'].$m[1];
        }
        else {
            if (strpos($asset, '$lang') !== false || strpos($asset, '$locale') !== false) {
                return 'k>'.$asset;
            }
            return 'b>'.$asset;
        }
    }
}
