<?php

/**
 * @author    Florian Lonqueu-Brochard
 * @copyright 2012 Florian Lonqueu-Brochard
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jPrefItem
{
    /**
     * @var string the identifiant of the preference
     */
    public $id;

    /**
     * @var string the value type of the preference : 'integer', 'decimal', 'string', 'boolean'
     */
    public $type = 'string';

    /**
     * @var mixed the value
     */
    public $value = '';

    /**
     * the locale selector for its label, shown into a form to set or see the value
     * (jpref_admin).
     */
    public $locale = '';

    /**
     * name of the group it belongs to. It allows jpref_admin to groups preferences.
     *
     * @var string
     */
    public $group = '';

    /**
     * the jacl2 subject for the right to see the value of the preference in
     * jpref_admin.
     * if empty: everybody can read the preference.
     *
     * @var string
     */
    public $read_acl_subject;

    /**
     * the jacl2 subject for the right to modify the value of the preference in
     * jpref_admin.
     * if empty: everybody can modify the preference.
     *
     * @var string
     */
    public $write_acl_subject;

    /**
     * @var mixed the default value of the preference
     */
    public $default_value;

    protected $_writable;

    protected $_readable;

    public static $allowed_types = array('integer', 'decimal', 'string', 'boolean');

    /**
     * Current user can read this pref.
     */
    public function isReadable()
    {
        return $this->_readable;
    }

    /**
     * Current user can write this pref.
     */
    public function isWritable()
    {
        return $this->_writable;
    }

    /**
     * Initialise the pref with a node from an ini content.
     *
     * @param string $node_key the name of the section ("pref:something")
     * @param array  $node     list of key/value
     */
    public function setFromIniNode($node_key, $node)
    {
        $this->id = substr($node_key, 5);

        if (!empty($node['type'])) {
            if (in_array($node['type'], self::$allowed_types)) {
                $this->type = $node['type'];
            } else {
                throw new jException('jpref~prefs.type.not.allowed', array($node['type'], implode(',', self::$allowed_types)));
            }
        }

        if (!empty($node['locale'])) {
            $this->locale = $node['locale'];
        }

        if (!empty($node['group'])) {
            $this->group = $node['group'];
        }

        $this->_readable = empty($node['read_acl_subject']) || jAcl2::check($node['read_acl_subject']);

        $this->_writable = empty($node['write_acl_subject']) || jAcl2::check($node['write_acl_subject']);

        if (!empty($node['default_value'])) {
            $this->default_value = $node['default_value'];
        }

        if ($this->type == 'boolean') {
            if ($this->default_value == 'true' || $this->default_value == '1') {
                $this->default_value = true;
            } elseif ($this->default_value == 'false' || (isset($node['default_value']) && $node['default_value'] == '')) {
                $this->default_value = false;
            }
        }
    }

    /**
     * Load the value of the pref via jPref.
     */
    public function loadValue()
    {
        $this->value = jPref::get($this->id);
    }

    /**
     * Compare 2 group.
     *
     * @param jPrefItemGroup $a the first group
     * @param jPrefItemGroup $b the second group
     *
     * @deprecated 1.7.0
     */
    public static function compareGroup($a, $b)
    {
        return jPrefItemGroup::compareGroup($a, $b);
    }
}
