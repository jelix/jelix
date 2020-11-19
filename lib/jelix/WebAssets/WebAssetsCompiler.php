<?php
/**
 * @package     jelix
 * @subpackage  WebAssets
 *
 * @author      Laurent Jouanneau
 * @copyright   2019 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\WebAssets;

class WebAssetsCompiler
{
    protected $config;

    /**
     * WebAssetsCompiler constructor.
     */
    public function __construct()
    {
    }

    protected $collections = array();

    /**
     * @param object $configuration
     * @param bool   $storeIntoConfiguration
     *
     * @return \StdClass data to use with WebAssetsSelector
     */
    public function compile($configuration, $storeIntoConfiguration = true)
    {
        $this->config = $configuration;
        $this->collections = array('common' => array());

        $vars = get_object_vars($this->config);
        // read common collection
        if (isset($vars['webassets_common'])) {
            $dummyCommon = array();
            $commonCollection = $this->parseAssetsSet('webassets_common', $dummyCommon);
            $this->collections['common'] = $commonCollection;
        }

        // read all collections

        foreach (array_keys($vars) as $section) {
            if (strpos($section, 'webassets_') !== 0 || $section == 'webassets_common') {
                continue;
            }
            $this->collections[substr($section, 10)] = $this->parseAssetsSet($section, $this->collections['common']);
        }

        if ($storeIntoConfiguration) {
            $compilation = $configuration;
        } else {
            $compilation = new \StdClass();
        }
        foreach ($this->collections as $name => $collection) {
            $collectionName = 'compiled_webassets_'.$name;

            $order = $this->getDependenciesOrder($collection);
            $compilation->{$collectionName} = array(
                'dependencies_order' => $order,
            );

            foreach ($collection as $groupName => $assets) {
                list($deps, $js, $css) = $this->getGroupProperties($name, $groupName);
                $compilation->{$collectionName}['webassets_'.$groupName.'.deps'] = $deps;
                $compilation->{$collectionName}['webassets_'.$groupName.'.js'] = $js;
                $compilation->{$collectionName}['webassets_'.$groupName.'.css'] = $css;
            }
        }

        return $compilation;
    }

    protected function parseAssetsSet($sectionName, &$commonCollection)
    {

        // read all assets groups
        $assetsGroups = array();
        foreach ($this->config->{$sectionName} as $prop => $val) {
            if (!preg_match('/^(.+)\\.([a-z]+)$/', $prop, $m)) {
                continue;
            }
            $groupName = $m[1];
            $property = $m[2];
            if (!isset($assetsGroups[$groupName])) {
                $assetsGroups[$groupName] = array(
                    'css' => array(),
                    'js' => array(),
                    'include' => array(),
                    'require' => array(),
                    // conditional require (reverse include)
                    'require_cond' => array(),
                );
            }

            $values = array();
            if ($property == 'css' || $property == 'js') {
                if (!is_array($val)) {
                    $val = array($val);
                }

                foreach ($val as $assetItem) {
                    $list = preg_split('/ *, */', $assetItem);
                    foreach ($list as $asset) {
                        if ($asset != '') {
                            $values[] = $this->parseAsset($asset);
                        }
                    }
                }
            } elseif ($property == 'include' || $property == 'require') {
                if (!is_array($val)) {
                    $val = array($val);
                }
                foreach ($val as $depGroupName) {
                    $values = array_merge($values, preg_split('/ *, */', $depGroupName));
                }
                $values = array_unique($values);
            }

            $assetsGroups[$groupName][$property] = $values;
        }

        if (count($commonCollection)) {
            // backport all common assets groups that are not already redefined
            // into the current assets set.
            foreach ($commonCollection as $groupName => $properties) {
                if (!isset($assetsGroups[$groupName])) {
                    $assetsGroups[$groupName] = $properties;
                    $assetsGroups[$groupName]['require_cond'] = array();
                }
            }
        }

        // remove dependencies that don't exist
        foreach ($assetsGroups as $groupName => $properties) {
            $assetsGroups[$groupName]['require'] = array_filter(
                $assetsGroups[$groupName]['require'],
                function ($depGroupName) use ($assetsGroups) {
                    return isset($assetsGroups[$depGroupName]);
                }
            );
            $assetsGroups[$groupName]['include'] = array_filter(
                $assetsGroups[$groupName]['include'],
                function ($depGroupName) use (&$assetsGroups, $groupName) {
                    if (isset($assetsGroups[$depGroupName])) {
                        $assetsGroups[$depGroupName]['require_cond'][] = $groupName;

                        return true;
                    }

                    return false;
                }
            );
        }

        return $assetsGroups;
    }

    protected function parseAsset($asset)
    {
        if (strpos($asset, '|') !== false) {
            list($asset, $attributes) = explode('|', $asset, 2);
            $attributes = '>'.$attributes;
        } else {
            $attributes = '>';
        }

        if (strpos($asset, '$jelix') !== false) {
            $asset = str_replace('$jelix', rtrim($this->config->urlengine['jelixWWWPath'], '/'), $asset);
        }
        if ($asset[0] == '/' || preg_match('!^https?://!', $asset)) {
            if (strpos($asset, '$lang') !== false || strpos($asset, '$locale') !== false) {
                return 'l>'.$asset.$attributes;
            }

            return 'u>'.$asset.$attributes;
        }
        if (preg_match('/^([a-zA-Z0-9_\\.]+)~([a-zA-Z0-9_:]+)(?:@([a-zA-Z0-9_]+))?$/', $asset)) {
            return 'a>'.$asset.$attributes;
        }
        if (preg_match('/^([a-zA-Z0-9_\\.]+):/', $asset)) {
            return 'm>'.$asset.$attributes;
        }
        if (strpos($asset, '$theme') === 0) {
            return 't>'.$asset.$attributes;
        }

        if (strpos($asset, '$lang') === false && strpos($asset, '$locale') === false) {
            return 'k>'.$asset.$attributes;
        }

        return 'b>'.$asset.$attributes;
    }

    protected function getDependenciesOrder($collection)
    {
        $this->assets = $collection;

        $this->groupsOrder = array();
        $this->includedAssetsGroups = array();
        foreach ($collection as $groupName => $assets) {
            $this->includeAssetsGroup($groupName);
        }

        return $this->groupsOrder;
    }

    protected $includedAssetsGroups = array();
    protected $groupsOrder = array();
    protected $assets = array();

    protected function includeAssetsGroup($group)
    {
        if (isset($this->includedAssetsGroups[$group])) {
            // avoid circular dependencies
            return;
        }

        $this->includedAssetsGroups[$group] = true;
        if (count($this->assets[$group]['require'])) {
            foreach ($this->assets[$group]['require'] as $assetGroup) {
                $this->includeAssetsGroup($assetGroup);
            }
        }

        if (count($this->assets[$group]['require_cond'])) {
            foreach ($this->assets[$group]['require_cond'] as $assetGroup) {
                $this->includeAssetsGroup($assetGroup);
            }
        }

        $this->groupsOrder[] = $group;
    }

    protected function getGroupProperties($collectionName, $groupName)
    {
        $this->assets = $this->collections[$collectionName];
        $this->groupsOrder = array();
        $this->includedAssetsGroups = array();

        return array(
            $this->getGroupDependencies($groupName),
            $this->assets[$groupName]['js'],
            $this->assets[$groupName]['css'],
        );
    }

    protected function getGroupDependencies($group)
    {
        if (isset($this->includedAssetsGroups[$group])) {
            // avoid circular dependencies
            return array();
        }

        $this->includedAssetsGroups[$group] = true;
        $requires = array();
        if (count($this->assets[$group]['require'])) {
            foreach ($this->assets[$group]['require'] as $assetGroup) {
                $req = $this->getGroupDependencies($assetGroup);
                $requires[] = $assetGroup;
                $requires = array_merge($requires, $req);
            }
        }

        if (count($this->assets[$group]['include'])) {
            foreach ($this->assets[$group]['include'] as $assetGroup) {
                $req = $this->getGroupDependencies($assetGroup);
                $requires[] = $assetGroup;
                $requires = array_merge($requires, $req);
            }
        }

        return array_unique($requires);
    }
}
