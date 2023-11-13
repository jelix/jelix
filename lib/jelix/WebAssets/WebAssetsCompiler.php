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

/**
 * It reads webassets configuration and transform this configuration into
 * a list that is readable very fast during the response construction.
 *
 * `webassets_*` section ar webassets collections. The webassets_common section
 * is collection that is merged with any other collections.
 *
 * In each section there can be several group of assets.
 * A group is often corresponding to a list of CSS and JS links used by a specific component.
 *
 * To declare JS files of a group, you have to use the name of the group, following by .js.
 * To declare CSS files, use the suffix `.css`.
 * To declare icon files, use the suffix `.icon`.
 *
 * For example, to declare CSS and JS links for the group `mygroup`:
 *
 * mygroup.js= "js/scripts.js"
 * mygroup.css= "design/super.css"
 * mygroup.icon= "favicon.png"
 *
 * List of assets is supported, using a comma or the array notation of the ini format:
 *
 * mygroup1.js= "js/foo.js, js/bar.js"
 *
 * mygroup2.js[]= "js/bla.js"
 * mygroup2.js[]= "js/baz.js"
 *
 * The path for JS and CSS links, can be:
 *
 * - an absolute path, starting with /, or a full URL (starting with http:// )
 * - a path relative to the base path (so it doesn't start with /)
 * - a path starting with $theme/, where $theme will be replaced by a path like <basepath>/themes/<current_theme>/
 * - a path starting with $jelix/, where $jelix will be replaced by the path of the jelix-www directory.
 * - a selector of a module action: <module>~<controller>:<method>. This action should return a JS or CSS content.
 * - a module name, followed by : and then by a path. This path should correspond to a file inside the www/ directory of the indicated module.
 *
 * Path like 1) to 4) can contain also some variables, $locale or $lang, that will be
 * replaced respectively by the current locale code (xx_YY) and the current language code (xx).
 *
 * Example:
 *
 * example.js[]= "/absolute/path.js"
 * example.js[]= "http://my.site/absolute/path.js"
 * example.js[]= related/to/basepath
 * example.js[]= "module:path/to/file.js, module~ctrl:meth"
 * example.js[]= "$theme/path/to/file.js, path/$lang/machin.js, /$locale/truc.js"
 *
 * some html attributes can be indicated, which will be added to the script or the link element, like defer or media attributes:
 *
 * example.js[]= "myscript.js|defer"
 * example.js[]= "https://popular.com/script.js.js|defer|integrity=sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K|crossorigin=anonymous"
 * example.js[]= "mymodule.mjs|type=module"
 * example.css[]= "mystyle.css|media=screen and (max-width: 600px)"
 * example.css[]= "fancy.css|rel=alternate stylesheet|title=Fancy"
 * example.icon[]= "favicon-32x32.png|sizes=32x32"
 * example.icon[]= "favicon-64x64.png|sizes=64x64"
 *
 *
 * A group can be included into an other group. For example, the assets group "mygroup"
 * must always used with the group "jquery". A dependency should then be indicated.
 *
 * Two kind of dependencies: require and include.
 *
 *
 * example1.js = ex1.js
 * example2.js = ex2.js
 * example2.css = ex2.css
 * example3.js = ex3.js
 * example3.css = ex3.css
 *
 * example.require = example1, example2
 * example.include = example3
 * example.js = foo.js
 * example.css = foo.css
 *
 * `.require` allows to include given groups before the declaration of JS and CSS files of the group into the HTML.
 * Whereas `.include` allows to include given groups after the assets of the group.
 */
class WebAssetsCompiler
{
    protected $config;

    /**
     * @var string the assetsRevQueryUrl configuration value, e.g. something like '_r=1234'.
     *             It may be empty.
     */
    protected $revisionQueryUrlParam;

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
        $this->revisionQueryUrlParam = $configuration->urlengine['assetsRevQueryUrl'];
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
                list($deps, $js, $css, $icon) = $this->getGroupProperties($name, $groupName);
                $compilation->{$collectionName}['webassets_'.$groupName.'.deps'] = $deps;
                $compilation->{$collectionName}['webassets_'.$groupName.'.js'] = $js;
                $compilation->{$collectionName}['webassets_'.$groupName.'.css'] = $css;
                $compilation->{$collectionName}['webassets_'.$groupName.'.icon'] = $icon;
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
                    'icon' => array(),
                    'include' => array(),
                    'require' => array(),
                    // conditional require (reverse include)
                    'require_cond' => array(),
                );
            }

            $values = array();
            if ($property == 'css' || $property == 'js' || $property == 'icon') {
                if (!is_array($val)) {
                    $val = array($val);
                }

                foreach ($val as $assetItem) {
                    $list = preg_split('/ *, */', $assetItem);
                    foreach ($list as $asset) {
                        if ($asset != '') {
                            $values[] = $this->parseAsset($asset, $property);
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

    protected function appendRevisionToUrl($url)
    {
        if ($this->revisionQueryUrlParam != '') {
            $url .= (strpos($url, '?') === false) ? '?' : '&';
            $url .= $this->revisionQueryUrlParam;
        }

        return $url;
    }

    protected function parseAsset($asset, $type)
    {
        if (strpos($asset, '|') !== false) {
            list($asset, $attributes) = explode('|', $asset, 2);
        } else {
            $attributes = '';
        }

        if ($type == 'icon' && strpos($attributes, 'type=') === false) {
            if (preg_match('/\\.png$/', $asset)) {
                if ($attributes) {
                    $attributes .= '|type=image/png';
                }
                else {
                    $attributes .= 'type=image/png';
                }
            }
        }

        $attributes = '>'.$attributes;

        $isHttp = preg_match('!^https?://!', $asset);
        if (!$isHttp) {
            $asset = $this->appendRevisionToUrl($asset);
        }

        if (strpos($asset, '$jelix') !== false) {
            $asset = str_replace('$jelix', rtrim($this->config->urlengine['jelixWWWPath'], '/'), $asset);
        }
        if ($asset[0] == '/' || $isHttp) {
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
            $this->assets[$groupName]['icon'],
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
