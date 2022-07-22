<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Yannick Le Guédart, Julien Issler
 *
 * @copyright   2011-2014 Laurent Jouanneau, 2007 Yannick Le Guédart, 2011 Julien Issler
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core;

use Jelix\Profiles\ProfilesContainer;
use Jelix\Profiles\ProfilesReader;
use Jelix\Profiles\ReaderPlugin;


/**
 * class to read profiles from the profiles.ini.php.
 */
class Profiles
{
    /**
     * loaded profiles.
     *
     * @var ProfilesContainer
     */
    protected static $_profiles;

    /**
     * @var \Jelix\Profiles\ReaderPlugin[]
     */
    protected static $_plugins = array();


    protected static function loadProfiles()
    {
        $file = App::varConfigPath('profiles.ini.php');
        $tempFile = App::tempPath('profiles.cache.php');

        $compiler = new ProfilesReader(function($name) {

            if (!isset(self::$_plugins[$name])) {
                $plugin = App::loadPlugin($name, 'profiles', '.profiles.php', $name.'ProfilesCompiler', $name);
                if (!$plugin) {
                    $plugin = new ReaderPlugin($name);
                }
                self::$_plugins[$name] = $plugin;
            }
            return self::$_plugins[$name];
        });

        self::$_profiles = $compiler->readFromFile($file, $tempFile);
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
     * @throws \jException
     *
     * @return array properties
     */
    public static function get($category, $name = '', $noDefault = false)
    {
        if (self::$_profiles === null) {
            self::loadProfiles();
        }

        return self::$_profiles->get($category, $name, $noDefault);
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
        if (self::$_profiles === null) {
            self::loadProfiles();
        }

        self::$_profiles->storeInPool($category, $name, $object);
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
        if (self::$_profiles === null) {
            self::loadProfiles();
        }

        return self::$_profiles->getFromPool($category, $name);
    }

    /**
     * add an object in the objects pool, corresponding to a profile
     * or store the object retrieved from the function, which accepts a profile
     * as parameter (array).
     *
     * @param string       $category  the profile category
     * @param string       $name      the name of the profile (will be given to Profiles::get)
     * @param array|string $function  the function name called to retrieved the object. It uses call_user_func.
     * @param bool         $noDefault if true and if the profile doesn't exist, throw an error instead of getting the default profile
     * @param mixed        $nodefault
     *
     * @return null|object the stored object
     */
    public static function getOrStoreInPool($category, $name, $function, $nodefault = false)
    {
        if (self::$_profiles === null) {
            self::loadProfiles();
        }

        return self::$_profiles->getOrStoreInPool($category, $name, $function, $nodefault);
    }

    /**
     * create a temporary new profile.
     *
     * @param string       $category the profile category
     * @param string       $name     the name of the profile
     * @param array|string $params   parameters of the profile. key=parameter name, value=parameter value.
     *                               we can also indicate a name of an other profile, to create an alias
     *
     * @throws \jException
     */
    public static function createVirtualProfile($category, $name, $params)
    {
        if (self::$_profiles === null) {
            self::loadProfiles();
        }

        self::$_profiles->createVirtualProfile($category, $name, $params);
    }

    /**
     * clear the loaded profiles to force to reload the profiles file.
     * WARNING: it destroy all objects stored in the pool!
     */
    public static function clear()
    {
        self::$_profiles = null;
    }
}
