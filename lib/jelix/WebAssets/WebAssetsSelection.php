<?php
/**
 * @package     jelix
 * @subpackage  WebAssets
 *
 * @author      Laurent Jouanneau
 * @copyright   2019-2023 Laurent Jouanneau
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
    protected $iconAssets = array();

    protected $variables;
    protected $urlBasePath;

    public function compute($config, $collectionName, $urlBasePath, $variables)
    {
        $this->variables = $variables;
        $this->urlBasePath = $urlBasePath;
        $this->cssAssets = array();
        $this->jsAssets = array();
        $this->iconAssets = array();
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
            $this->iconAssets = array_merge(
                $this->iconAssets,
                $collectionAssets['webassets_'.$group.'.icon']
            );
        }
        $me = $this;
        $this->jsAssets = array_map(function ($js) use ($me) {
            return $me->parseAssetUrl($js);
        }, $this->jsAssets);
        $this->cssAssets = array_map(function ($css) use ($me) {
            return $me->parseAssetUrl($css);
        }, $this->cssAssets);
        $this->iconAssets = array_map(function ($icon) use ($me) {
            return $me->parseAssetUrl($icon);
        }, $this->iconAssets);
    }

    /**
     * List of urls and corresponding attributes for the script element.
     *
     * @return array[] list of array(url, attributes)
     */
    public function getJsLinks()
    {
        return $this->jsAssets;
    }

    /**
     * List of url and corresponding attributes for the link element for CSS style sheets.
     *
     * @return array[] list of array(url, attributes)
     */
    public function getCssLinks()
    {
        return $this->cssAssets;
    }

    /**
     * List of url and corresponding attributes for the link element for favicons
     *
     * @return array[] list of array(url, attributes)
     */
    public function getIconLinks()
    {
        return $this->iconAssets;
    }

    protected function parseAssetUrl($asset)
    {
        list($assetURLType, $resource, $attributes) = explode('>', $asset, 3);
        if ($attributes) {
            $attrs = explode('|', $attributes);
            $attributes = array();
            foreach ($attrs as $attr) {
                $attr = explode('=', $attr);
                if (count($attr) == 1) {
                    $attributes[$attr[0]] = true;
                } else {
                    $attributes[$attr[0]] = $attr[1];
                }
            }
        } else {
            $attributes = array();
        }

        switch ($assetURLType) {
            case 'k': // relative path to base path
                $url = $this->urlBasePath.$resource;

                break;

            case 'b': // relative path to base path with lang/locale
                $url = $this->urlBasePath.
                    str_replace(
                        array('$lang', '$locale'),
                        array($this->variables['$lang'], $this->variables['$locale']),
                        $resource
                    );

                break;

            case 'a': // action
                $url = \jUrl::get($resource);

                break;

            case 'm': // resource file stored in a module
                list($module, $src) = explode(':', $resource, 2);

                $url = \jUrl::get('jelix~www:getfile', array('targetmodule' => $module, 'file' => $src));

                break;

            case 't': // theme url with probably  lang/locale
                $url = str_replace(
                    array('$lang', '$locale', '$theme'),
                    array($this->variables['$lang'], $this->variables['$locale'],
                        $this->urlBasePath.$this->variables['$theme'], ),
                    $resource
                );

                break;

            case 'l': // absolute url with lang/locale/theme/jelix path
                $url = str_replace(
                    array('$lang', '$locale'),
                    array($this->variables['$lang'], $this->variables['$locale']),
                    $resource
                );

                break;

            case 'u': // absolute url
            default:
                $url = $resource;
        }

        return array($url, $attributes);
    }
}
