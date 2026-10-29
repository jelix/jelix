<?php
/**
 * @package    jelix
 * @subpackage auth_driver
 *
 * @author     Tahina Ramaroson
 * @contributor Sylvain de Vathaire
 * @contributor Thibaud Fabre, Laurent Jouanneau
 *
 * @copyright  2009 Neov, 2010 Thibaud Fabre, 2011-2023 Laurent Jouanneau
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * LDAP authentification driver for authentification information stored in LDAP server.
 *
 * @package    jelix
 * @subpackage auth_driver
 */
class ldapAuthDriver extends jAuthDriverBase implements jIAuthDriver
{
    /**
     * default user attributes list.
     *
     * @var array
     */
    protected $_default_attributes = array('cn', 'distinguishedName', 'name');

    protected $uriConnect = '';

    public function __construct($params)
    {
        if (!extension_loaded('ldap')) {
            throw new jException('jelix~auth.ldap.extension.unloaded');
        }

        parent::__construct($params);

        if (!isset($this->_params['profile']) || $this->_params['profile'] == '') {
            throw new jException('jelix~auth.ldap.profile.missing');
        }

        $profile = jProfiles::get('authldap', $this->_params['profile']);
        $this->_params = array_merge($this->_params, $profile);

        // default ldap parameters
        $_default_params = array(
            'hostname' => 'localhost',
            'tlsMode'       => '',
            'port' => 389,
            'ldapUser' => null,
            'ldapPassword' => null,
            'protocolVersion' => 3,
            'uidProperty' => 'cn',
            'ldapUserObjectClass' => 'user',
        );

        // iterate each default parameter and apply it to actual params if missing in $params.
        foreach ($_default_params as $name => $value) {
            if (!isset($this->_params[$name]) || $this->_params[$name] == '') {
                $this->_params[$name] = $value;
            }
        }

        if (!isset($this->_params['searchBaseDN']) || $this->_params['searchBaseDN'] == '') {
            throw new jException('jelix~auth.ldap.search.base.missing');
        }

        if (!isset($this->_params['searchFilter']) || $this->_params['searchFilter'] == '') {
            throw new jException('jelix~auth.ldap.search.filter.missing');
        }

        if (!isset($this->_params['searchAttributes']) || $this->_params['searchAttributes'] == '') {
            $this->_params['searchAttributes'] = $this->_default_attributes;
        } else {
            $this->_params['searchAttributes'] = explode(',', $this->_params['searchAttributes']);
        }

        $uri = $this->_params['hostname'];

        if (preg_match('!^ldap(s?)://!', $uri, $m)) { // old way to specify ldaps protocol
            $predefinedPort = '';
            if (preg_match('!:(\d+)/?!', $uri, $mp)) {
                $predefinedPort = $mp[1];
            }
            if (isset($m[1]) && $m[1] == 's') {
                $this->_params['tlsMode'] = 'ldaps';
            }
            elseif ($this->_params['tlsMode'] == 'ldaps') {
                $this->_params['tlsMode'] = 'starttls';
            }
            if ($predefinedPort == '') {
                $uri .= ':'.$this->_params['port'];
            }
            else {
                $this->_params['port'] = $predefinedPort;
            }
            $this->uriConnect = $uri;
        }
        else {
            $uri .= ':'.$this->_params['port'];
            if ($this->_params['tlsMode'] == 'ldaps' || $this->_params['port'] == 636 ) {
                $this->uriConnect = 'ldaps://'.$uri;
                $this->_params['tlsMode'] = 'ldaps';
            }
            else {
                $this->uriConnect = 'ldap://'.$uri;
            }
        }
    }

    public function saveNewUser($user)
    {
        if (!is_object($user) || !($user instanceof jAuthUserLDAP)) {
            throw new jException('jelix~auth.ldap.object.user.unknown');
        }

        if (!$this->isValidLogin($user->login)) {
            throw new jException('jelix~auth.ldap.user.login.unset');
        }

        $entries = $this->getAttributesLDAP($user);

        $connect = $this->_bindLdapUser();
        if ($connect === false) {
            return false;
        }

        $dn = $this->_buildUserDn($user->login);
        if ($dn) {
            $result = ldap_add($connect, $dn, $entries);
        }
        else {
            $result = false;
        }
        ldap_close($connect);

        return $result;
    }

    public function removeUser($login)
    {
        $login = trim($login, " ");
        if (!$this->isValidLogin($login)) {
            throw new Exception('ldap: bad login parameter');
        }

        $connect = $this->_bindLdapUser();
        if ($connect === false) {
            return false;
        }

        $dn = $this->_buildUserDn($login);
        if ($dn) {
            $result = ldap_delete($connect, $dn);
        }
        else {
            $result = false;
        }
        ldap_close($connect);

        return $result;
    }

    public function updateUser($user)
    {
        if (!is_object($user) || !($user instanceof jAuthUserLDAP)) {
            throw new jException('jelix~auth.ldap.object.user.unknown');
        }

        $login = trim($user->login, " ");
        if (!$this->isValidLogin($login)) {
            throw new Exception('ldap: bad login parameter');
        }

        $entries = $this->getAttributesLDAP($user, true);

        $connect = $this->_bindLdapUser();
        if ($connect === false) {
            return false;
        }
        $dn = $this->_buildUserDn($user->login);
        if ($dn) {
            $result = ldap_modify($connect, $dn, $entries);
        }
        else {
            $result = false;
        }
        ldap_close($connect);

        return $result;
    }

    public function getUser($login)
    {
        $login = trim($login, " ");
        if (!$this->isValidLogin($login)) {
            throw new Exception('ldap: bad login parameter');
        }

        $connect = $this->_bindLdapUser();
        if ($connect === false) {
            return false;
        }

        $entry = $this->searchLdapUser($connect, $login);

        if ($entry) {
            $user = $this->loadLdapAttributes($connect, $entry);
            if ($user) {
                $user->login = $login;
                ldap_close($connect);

                return $user;
            }
        }

        ldap_close($connect);

        return false;
    }

    public function createUserObject($login, $password)
    {
        $login = trim($login, " ");
        if (!$this->isValidLogin($login)) {
            throw new Exception('ldap: bad login parameter');
        }

        $user = new jAuthUserLDAP();

        $user->login = $login;
        $user->password = $this->cryptPassword($password);
        foreach ($this->_params['searchAttributes'] as $property) {
            $user->{$property} = '';
        }

        return $user;
    }

    public function getUserList($pattern)
    {
        $users = array();

        $connect = $this->_bindLdapUser();
        if ($connect === false) {
            return $users;
        }
        $filter = ($pattern != '' && $pattern != '%') ?
            '(&'.$this->_params['searchFilter']."({$this->_params['uidProperty']}={$pattern}))" :
            $this->_params['searchFilter'];

        if (($search = ldap_search($connect, $this->_params['searchBaseDN'], $filter, $this->_params['searchAttributes']))) {
            $entry = ldap_first_entry($connect, $search);
            while ($entry) {
                $user = $this->loadLdapAttributes($connect, $entry);
                if ($user) {
                    $users[] = $user;
                }
                $entry = ldap_next_entry($connect, $entry);
            }
        }
        ldap_close($connect);

        usort($users, function ($a, $b) {
            return strcmp($a->login, $b->login);
        });

        return $users;
    }

    public function changePassword($login, $newpassword)
    {
        $login = trim($login, " ");
        if (!$this->isValidLogin($login)) {
            throw new Exception('ldap: bad login parameter');
        }
        $connect = $this->_bindLdapUser();
        if ($connect === false) {
            return false;
        }

        // see if the user exists into the ldap directory
        $ldapEntry = $this->searchLdapUser($connect, $login);
        if ($ldapEntry === false) {
            return false;
        }

        $dn = $this->getEntryDn($connect, $ldapEntry, $login);

        $result = @ldap_modify($connect, $dn, array(
            "userPassword" => $this->cryptPassword($newpassword)
        ));

        ldap_close($connect);

        if ($result === false) {
            throw new Exception('ldap: error when trying to change password of user "'.$login.'"');
        }

        return true;
    }

    public function verifyPassword($login, $password)
    {
        $login = trim($login, " ");
        if (!$this->isValidLogin($login)) {
            throw new Exception('ldap: bad login parameter');
        }

        $connect = $this->_getLinkId();

        if ($connect) {


            $dn = $this->_buildUserDn($login);
            if (!$dn) {
                return false;
            }

            //authenticate user
            $bind = @ldap_bind($connect, $dn, $this->cryptPassword($password));

            if ($bind) {
                //get connected user infos
                if ($this->_params['ldapUser'] == '') {
                    $bindAdmin = ldap_bind($connect);
                } else {
                    $bindAdmin = ldap_bind($connect, $this->_params['ldapUser'], $this->_params['ldapPassword']);
                }
                if ($bindAdmin) {
                    $entry = $this->searchLdapUser($connect, $login);

                    if ($entry) {
                        $user = $this->loadLdapAttributes($connect, $entry);
                        if ($user) {
                            $user->login = $login;
                            ldap_close($connect);

                            return $user;
                        }
                    }
                }
            }
            ldap_close($connect);
        }

        return false;
    }

    protected function getAttributesLDAP($user, $update = false)
    {
        $entries = array();
        $entries['objectclass'] = $this->_params['ldapUserObjectClass'];
        foreach ($this->_params['searchAttributes'] as $attribute) {
            switch (strtolower($attribute)) {
                case 'mail':
                    $entries['mail'] = $user->email;

                    break;

                case $this->_params['uidProperty']:
                    if (!$update) {
                        $entries[$this->_params['uidProperty']] = $user->login;
                    }

                    break;

                default:
                    if (isset($user->{$attribute}) && $user->{$attribute} != '') {
                        $entries[$attribute] = $user->{$attribute};
                    }

                    break;
            }
        }
        if (!$update && $user->password) {
            $entries['userPassword'] = $user->password;
        }

        return $entries;
    }

    protected function setAttributesLDAP(&$user, $attributes)
    {
        foreach ($this->_params['searchAttributes'] as $attribute) {
            if (isset($attributes[$attribute])) {
                $attr = $attributes[$attribute];
                if ($attr['count'] > 1) {
                    $val = array_shift($attr);
                } else {
                    $val = $attr[0];
                }

                switch (strtolower($attribute)) {
                    case 'mail':
                        $user->email = $val;

                        break;

                    case $this->_params['uidProperty']:
                        $user->login = $val;

                        break;

                    default:
                        $user->{$attribute} = $val;

                        break;
                }
            }
        }
    }

    protected function _buildUserDn($login)
    {
        if ($login) {
            return $this->_params['uidProperty'].'='.ldap_escape((string)$login, '', LDAP_ESCAPE_DN).','.$this->_params['searchBaseDN'];
        }

        return '';
    }

    protected function _getLinkId()
    {
        if ($connect = ldap_connect($this->uriConnect)) {
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, $this->_params['protocolVersion']);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

            if ($this->_params['tlsMode'] == 'starttls') {
                if (!@ldap_start_tls($connect)) {
                    jLog::log('Ldap connection error: impossible to start TLS connection to '.$this->uriConnect);
                    return false;
                }
            }

            return $connect;
        }

        return false;
    }

    protected function _bindLdapUser()
    {
        $connect = $this->_getLinkId();
        if (!$connect) {
            return false;
        }
        if ($this->_params['ldapUser'] == '') {
            $bind = ldap_bind($connect);
        } else {
            $bind = ldap_bind($connect, $this->_params['ldapUser'], $this->_params['ldapPassword']);
        }
        if (!$bind) {
            if ($this->_params['ldapUser'] == '') {
                jLog::log('ldap driver error: authenticating to the ldap with an anonymous user is not supported', 'auth');
            } else {
                jLog::log('ldap driver error: impossible to authenticate to ldap with the user '.$this->_params['ldapUser'], 'auth');
            }
            ldap_close($connect);

            return false;
        }

        return $connect;
    }

    /**
     * Check that the login does not contain characters we don't want
     *
     * Logins with spaces at the beginning or at the end are refused, as
     * many ldap servers ignore these spaces during comparison: the user could
     * be recognized with a login that is different from the one stored into the
     * directory.
     *
     * @param string $login
     * @return bool
     */
    protected function isValidLogin($login)
    {
        if ($login === '' || trim($login) !== $login) {
            return false;
        }
        // control characters (NUL, line feed...) are not allowed
        return !preg_match('/[\x00-\x1F\x7F]/', $login);
    }


    /**
     * Search an LDAP user
     *
     * @param \LDAP\Connection $connect
     * @param string $login
     * @return false|\LDAP\ResultEntry False if the user is not found, else the result entry
     * @throws Exception when the search fails, or when several users match the login
     */
    protected function searchLdapUser($connect, $login)
    {
        $search = ldap_search(
            $connect,
            $this->_params['searchBaseDN'],
            $this->_params['uidProperty'].'='.ldap_escape($login, '', LDAP_ESCAPE_FILTER),
            $this->_params['searchAttributes']
        );

        if ($search === false) {
            return false;
        }

        $count = ldap_count_entries($connect, $search);
        if ($count > 1) {
            // several entries match the login, ignore all;
            return false;
        }

        if ($count == 1) {
            return ldap_first_entry($connect, $search);
        }
        return false;
    }

    /**
     * @param \LDAP\Connection $connect
     * @param \LDAP\ResultEntry $entry
     * @param string $login
     * @return string
     * @throws Exception
     */
    protected function getEntryDn($connect, $entry, $login)
    {
        $dn = @ldap_get_dn($connect, $entry);
        if ($dn === false || $dn === '') {
            throw new Exception('ldap: cannot retrieve the DN of the user "'.$login.'"');
        }
        return $dn;
    }


    protected function loadLdapAttributes($connect, $entry)
    {
        $attributes = ldap_get_attributes($connect, $entry);
        if ($attributes === false) {
            return false;
        }

        if ($attributes['count'] > 0) {
            $user = new jAuthUserLDAP();
            $this->setAttributesLDAP($user, $attributes);
            $user->password = '';
            return $user;
        }

        return false;
    }
}
