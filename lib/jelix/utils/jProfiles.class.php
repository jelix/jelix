<?php
/**
 * @package     jelix
 * @subpackage  utils
 *
 * @author      Laurent Jouanneau
 * @contributor Yannick Le Guédart, Julien Issler
 *
 * @copyright   2011-2012 Laurent Jouanneau, 2007 Yannick Le Guédart, 2011 Julien Issler
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * class to read profiles from the profiles.ini.php.
 *
 * @package     jelix
 * @subpackage  utils
 */
class jProfiles
{
    /**
     * loaded profiles.
     *
     * @var array
     */
    protected static $_profiles;

    /**
     * pool of objects loaded for profiles.
     *
     * @var object[]
     */
    protected static $_objectPool = array();

    protected static function loadProfiles()
    {
        $file = jApp::varConfigPath('profiles.ini.php');
        $tempFile = jApp::tempPath('profiles.cache.php');
        if (!file_exists($tempFile) || filemtime($file) > filemtime($tempFile)) {
            $compiler = new jProfilesCompiler($file);
            self::$_profiles = $compiler->compile();
            \Jelix\IniFile\Util::write(self::$_profiles, $tempFile);
        } else {
            self::$_profiles = parse_ini_file($tempFile, true, INI_SCANNER_TYPED);
        }
    }

    /**
     * load properties of a profile.
     *
     * A profile is a section in the profiles.ini.php file. Profiles are belong
     * to a category. Each section names is composed by "category:profilename".
     *
     * The given name can be a profile name or an alias of a profile. An alias
     * is a parameter name in the category section of the ini file, and the value
     * of this parameter should be a profile name.
     *
     * @param string $category  the profile category
     * @param string $name      profile name or alias of a profile name. if empty, use the default profile
     * @param bool   $noDefault if true and if the profile doesn't exist, throw an error instead of getting the default profile
     *
     * @throws jException
     *
     * @return array properties
     */
    public static function get($category, $name = '', $noDefault = false)
    {
        if (self::$_profiles === null) {
            self::loadProfiles();
        }

        if ($name == '') {
            $name = 'default';
        }
        $section = $category.':'.$name;

        // the name attribute created in this method will be the name of the connection
        // in the connections pool. So profiles of aliases and real profiles should have
        // the same name attribute.

        if (isset(self::$_profiles[$section])) {
            return self::$_profiles[$section];
        }
        // if the profile doesn't exist, we take the default one
        if (!$noDefault && isset(self::$_profiles[$category.':default'])) {
            return self::$_profiles[$category.':default'];
        }

        if ($name == 'default') {
            throw new jException('jelix~errors.profile.default.unknown', $category);
        }

        throw new jException('jelix~errors.profile.unknown', array($name, $category));
    }

    /**
     * add an object in the objects pool, corresponding to a profile.
     *
     * @param string $category the profile category
     * @param string $name     the name of the profile  (value of _name in the retrieved profile)
     * @param object $obj      the object to store
     * @param mixed  $object
     */
    public static function storeInPool($category, $name, $object)
    {
        self::$_objectPool[$category][$name] = $object;
    }

    /**
     * get an object from the objects pool, corresponding to a profile.
     *
     * @param string $category the profile category
     * @param string $name     the name of the profile (value of _name in the retrieved profile)
     *
     * @return null|object the stored object
     */
    public static function getFromPool($category, $name)
    {
        if (isset(self::$_objectPool[$category][$name])) {
            return self::$_objectPool[$category][$name];
        }

        return null;
    }

    /**
     * add an object in the objects pool, corresponding to a profile
     * or store the object retrieved from the function, which accepts a profile
     * as parameter (array).
     *
     * @param string       $category  the profile category
     * @param string       $name      the name of the profile (will be given to jProfiles::get)
     * @param array|string $function  the function name called to retrieved the object. It uses call_user_func.
     * @param bool         $noDefault if true and if the profile doesn't exist, throw an error instead of getting the default profile
     * @param mixed        $nodefault
     *
     * @return null|object the stored object
     */
    public static function getOrStoreInPool($category, $name, $function, $nodefault = false)
    {
        $profile = self::get($category, $name, $nodefault);
        if (isset(self::$_objectPool[$category][$profile['_name']])) {
            return self::$_objectPool[$category][$profile['_name']];
        }
        $obj = call_user_func($function, $profile);
        if ($obj) {
            self::$_objectPool[$category][$profile['_name']] = $obj;
        }

        return $obj;
    }

    /**
     * create a temporary new profile.
     *
     * @param string       $category the profile category
     * @param string       $name     the name of the profile
     * @param array|string $params   parameters of the profile. key=parameter name, value=parameter value.
     *                               we can also indicate a name of an other profile, to create an alias
     *
     * @throws jException
     */
    public static function createVirtualProfile($category, $name, $params)
    {
        if ($name == '') {
            throw new jException('jelix~errors.profile.virtual.no.name', $category);
        }

        if (self::$_profiles === null) {
            self::loadProfiles();
        }

        if (is_string($params)) {
            if (isset(self::$_profiles[$category.':'.$params])) {
                self::$_profiles[$category.':'.$name] = self::$_profiles[$category.':'.$params];
            } else {
                throw new jException('jelix~errors.profile.unknown', array($params, $category));
            }
        } else {
            $plugin = jApp::loadPlugin($category, 'profiles', '.profiles.php', $category.'ProfilesCompiler', $category);
            if (!$plugin) {
                $plugin = new jProfilesCompilerPlugin($category);
            }

            if (isset(self::$_profiles[$category.':__common__'])) {
                $plugin->setCommon(self::$_profiles[$category.':__common__']);
            }
            $plugin->addProfile($name, $params);
            $plugin->getProfiles(self::$_profiles);
        }
        unset(self::$_objectPool[$category][$name]); // close existing connection with the same pool name
        if (gc_enabled()) {
            gc_collect_cycles();
        }
    }

    /**
     * clear the loaded profiles to force to reload the profiles file.
     * WARNING: it destroy all objects stored in the pool!
     */
    public static function clear()
    {
        self::$_profiles = null;
        self::$_objectPool = array();
        if (gc_enabled()) {
            gc_collect_cycles();
        }
    }
}
