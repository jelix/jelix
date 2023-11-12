<?php

/**
 * @author    Florian Lonqueu-Brochard
 * @contributor Laurent Jouanneau
 *
 * @copyright 2012 Florian Lonqueu-Brochard, 2016 Laurent Jouanneau
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jPrefManager
{
    protected static $_ini;

    protected static $_pref_config_file = 'preferences.ini.php';

    /**
     * Add a preference into the preference config.
     *
     * Should be used only during installation, as the configuration file
     * is in the app/system/ directory (readonly for the web server in theory).
     *
     * @param jPrefItem $preference the preference to add
     * @param mixed     $pref
     */
    public function addPreference($pref)
    {
        self::_loadIniModifier();

        $section = 'pref:'.$pref->id;

        self::$_ini->setValue('type', $pref->type, $section);
        self::$_ini->setValue('locale', $pref->locale, $section);
        self::$_ini->setValue('group', $pref->group, $section);
        self::$_ini->setValue('read_acl_subject', $pref->read_acl_subject, $section);
        self::$_ini->setValue('write_acl_subject', $pref->write_acl_subject, $section);
        self::$_ini->setValue('default_value', $pref->default_value, $section);

        self::$_ini->save(jApp::config()->chmodFile);

        if ($pref->value !== null) {
            jPref::set($pref->id, $pref->value);
        } elseif ($pref->default_value !== null) {
            jPref::set($pref->id, $pref->default_value);
        }
    }

    /**
     * Add a group of preference into the preference config.
     *
     * @param jPrefItemGroup $grp the preference group to add
     */
    public function addGroup($grp)
    {
        self::_loadIniModifier();

        $section = 'group:'.$grp->id;

        self::$_ini->setValue('locale', $grp->locale, $section);
        self::$_ini->setValue('order', $grp->order, $section);

        self::$_ini->save(jApp::config()->chmodFile);
    }

    /**
     * @param mixed $get_prefs_values
     */
    public static function getAllPreferences($get_prefs_values = true)
    {
        $preferences = self::_getPrefFile();
        $prefs = array();
        $nogroup = new jPrefItemGroup();
        $nogroup->id = '__nogroup';
        $nogroup->locale = 'jpref~prefs.group.others';

        foreach ($preferences as $item_key => $item) {
            if (substr($item_key, 0, 5) == 'group') {
                $g = new jPrefItemGroup();
                $g->setFromIniNode($item_key, $item);

                $prefs[$g->id] = $g;
            } elseif (substr($item_key, 0, 4) == 'pref') {
                $p = new jPrefItem();
                $p->setFromIniNode($item_key, $item);

                //current user doesnt have rights to read this pref
                if (!$p->isReadable()) {
                    continue;
                }

                if ($get_prefs_values) {
                    $p->loadValue();
                }

                if (!empty($p->group)) {
                    $prefs[$p->group]->prefs[] = $p;
                } else {
                    $nogroup->prefs[] = $p;
                }
            }
        }
        usort($prefs, 'jPrefItemGroup::compareGroup');
        if (count($nogroup->prefs) > 0) {
            $prefs['__nogroup'] = $nogroup;
        }

        return $prefs;
    }

    /**
     * @param mixed $pref_id
     * @param mixed $get_pref_value
     */
    public static function getPref($pref_id, $get_pref_value = true)
    {
        $preferences = self::_getPrefFile();

        $item_key = 'pref:'.$pref_id;

        if (isset($preferences[$item_key])) {
            $ini_node = $preferences[$item_key];
            $p = new jPrefItem();
            $p->setFromIniNode($item_key, $ini_node);

            //current user doesnt have rights to read this pref
            if (!$p->isReadable()) {
                return null;
            }

            if ($get_pref_value) {
                $p->loadValue();
            }

            return $p;
        }
    }

    /**
     * @since 1.6.5
     *
     * @param mixed $iniFile
     */
    public static function importFromIni($iniFile)
    {
        $ini = \Jelix\IniFile\Util::read($iniFile);
        if ($ini === false) {
            throw new Exception('Bad ini file: '.basename($iniFile));
        }
        foreach ($ini as $section => $node) {
            if (strpos($section, 'pref:') === 0) {
                $p = new jPrefItem();
                $p->setFromIniNode($section, $node);
                self::addPreference($p);
            } elseif (strpos($section, 'group:') === 0) {
                $p = new jPrefItemGroup();
                $p->setFromIniNode($section, $node);
                self::addGroup($p);
            }
        }
    }

    protected static function _getPrefFile()
    {
        return \Jelix\IniFile\Util::read(jApp::appSystemPath(self::$_pref_config_file));
    }

    protected function _loadIniModifier()
    {
        if (!self::$_ini) {
            self::$_ini = new \Jelix\IniFile\IniModifier(jApp::appSystemPath(self::$_pref_config_file));
        }

        return self::$_ini;
    }
}
