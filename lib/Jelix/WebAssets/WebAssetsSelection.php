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

class WebAssetsSelection
{
    protected $_assetsGroups = array();

    public function addAssetsGroup($assetGroup)
    {
        $this->_assetsGroups[] = $assetGroup;
    }

    protected $cssAssets = array();
    protected $jsAssets = array();

    protected $variables;
    protected $urlBasePath;

    public function compute($config, $collectionName, $urlBasePath, $variables)
    {
        $this->variables = $variables;
        $this->urlBasePath = $urlBasePath;
        $this->cssAssets = array();
        $this->jsAssets = array();
        if (!count($this->_assetsGroups)) {
            return;
        }

        $collectionAssets = $config->{'compiled_webassets_'.$collectionName};

        // retrieve all assets groups and their dependencies (as groups)
        $allGroups = array();
        foreach ($this->_assetsGroups as $group) {
            if (isset($collectionAssets['webassets_'.$group.'.deps'])) {
                $allGroups[] = $group;
                $allGroups = array_merge($allGroups, $collectionAssets['webassets_'.$group.'.deps']);
            }
        }
        $allGroups = array_unique($allGroups);

        $orderedSelection = array_intersect($collectionAssets['dependencies_order'], $allGroups);
        // retrieve assets in the right order
        foreach ($orderedSelection as $group) {
            $this->jsAssets = array_merge(
                $this->jsAssets,
                $collectionAssets['webassets_'.$group.'.js']
            );
            $this->cssAssets = array_merge(
                $this->cssAssets,
                $collectionAssets['webassets_'.$group.'.css']
            );
        }
        $me = $this;
        $this->jsAssets = array_map(function ($js) use ($me) {
            return $me->parseAssetUrl($js);
        }, $this->jsAssets);
        $this->cssAssets = array_map(function ($css) use ($me) {
            return $me->parseAssetUrl($css);
        }, $this->cssAssets);
    }

    public function getJsLinks()
    {
        return $this->jsAssets;
    }

    public function getCssLinks()
    {
        return $this->cssAssets;
    }

    protected function parseAssetUrl($asset)
    {
        list($assetURLType, $resource) = explode('>', $asset, 2);
        switch ($assetURLType) {
            case 'k': // relative path to base path
                return $this->urlBasePath.$resource;
            case 'b': // relative path to base path with lang/locale
                return $this->urlBasePath.
                    str_replace(
                        array('$lang', '$locale'),
                        array($this->variables['$lang'], $this->variables['$locale']),
                        $resource
                    );
            case 'a': // action
                return \jUrl::get($resource);
            case 'm': // resource file stored in a module
                list($module, $src) = explode(':', $resource, 2);

                return \jUrl::get('jelix~www:getfile', array('targetmodule' => $module, 'file' => $src));
            case 't': // theme url with probably  lang/locale
                return str_replace(
                    array('$lang', '$locale', '$theme'),
                    array($this->variables['$lang'], $this->variables['$locale'],
                        $this->variables['$theme'], ),
                    $resource
                );
            case 'l': // absolute url with lang/locale/theme/jelix path
                return str_replace(
                    array('$lang', '$locale'),
                    array($this->variables['$lang'], $this->variables['$locale']),
                    $resource
                );
            case 'u': // absolute url
            default:
                return $resource;
        }
    }
}
