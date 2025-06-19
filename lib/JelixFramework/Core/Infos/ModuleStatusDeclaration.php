<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2009-2023 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Core\Infos;

/**
 * It represents the state of the module, as declared into the framework.ini.php file
 */
class ModuleStatusDeclaration
{
    /**
     * name of the module
     */
    public readonly string $name;

    /**
     * indicate if the module is enabled into the application or not.
     */
    public readonly bool $isEnabled;

    /**
     * database profile to use
     */
    public readonly string $dbProfile;

    /**
     * @var string[] parameters for installation
     */
    public readonly array $parameters;

    public readonly bool $skipInstaller;

    public readonly string $path;

    public readonly bool $isNative;

    /**
     * @param string $name   the name of the module
     * @param string $path   the path to the module
     * @param array  $config configuration of modules (the [module:$name] section of framework.ini),
     * @param boolean $isNativeModule true if this is a module installed natively into the application
     *   (versus a module installed into an instance of the application)
     */
    public function __construct($name, $config, $isNativeModule = true)
    {
        $this->name = $name;
        if (isset($config['enabled'])) {
            $this->isEnabled = $config['enabled'];
        }
        else {
            $this->isEnabled = false;
        }

        if (isset($config['dbprofile'])) {
            $this->dbProfile = $config['dbprofile'];
        }
        else {
            $this->dbProfile = '';
        }

        if (isset($config['installparam'])) {
            $this->parameters = self::unserializeParameters($config['installparam']);
        }
        else {
            $this->parameters = array();
        }

        if (isset($config['path'])) {
            $this->path = (string) $config['path'];
        }
        else {
            $this->path = '';
        }

        if (isset($config['skipinstaller']) && $config['skipinstaller'] == 'skip') {
            $this->skipInstaller = true;
        }
        else {
            $this->skipInstaller = false;
        }

        $this->isNative = $isNativeModule;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Return list of configuration parameters as stored into the ini file.
     *
     * @return array
     */
    public function getValuesForIni()
    {
        $values = array(
            'enabled' => $this->isEnabled,
        );

        if ($this->path) {
            $values['path'] = $this->path;
        }

        if ($this->dbProfile) {
            $values['dbProfile'] = $this->dbProfile;
        }

        if ($this->parameters) {
            $values['installparam'] = ModuleStatusDeclaration::serializeParametersAsArray($this->parameters);
        }

        if ($this->skipInstaller) {
            $values['skipinstaller'] = 'skip';
        }

        return $values;
    }

    /**
     * Unserialize parameters coming from the ini file.
     *
     * Parameters could be fully serialized into a single string, or
     * could be as an associative array where only values are serialized
     *
     * @param array|string $parameters
     *
     * @return array
     */
    public static function unserializeParameters($parameters)
    {
        $trueParams = array();
        if (!is_array($parameters)) {
            $parameters = trim($parameters);
            if ($parameters == '') {
                return $trueParams;
            }
            $params = array();
            foreach (explode(';', $parameters) as $param) {
                $kp = explode('=', $param);
                if (count($kp) > 1) {
                    $params[$kp[0]] = $kp[1];
                } else {
                    $params[$kp[0]] = true;
                }
            }
        } else {
            $params = $parameters;
        }

        foreach ($params as $key => $v) {
            if (is_string($v) && (strpos($v, ',') !== false || (strlen($v) && $v[0] == '['))) {
                $trueParams[$key] = explode(',', trim($v, '[]'));
            } elseif ($v === 'false') {
                $trueParams[$key] = false;
            } elseif ($v === 'true') {
                $trueParams[$key] = true;
            } else {
                $trueParams[$key] = $v;
            }
        }

        return $trueParams;
    }

    /**
     * Serialize parameters to be stores into an ini file.
     *
     * The result is a single string with fully serialized array as found
     * in Jelix 1.6 or lower.
     *
     * @param array $parameters
     * @param array $defaultParameters
     *
     * @return string
     */
    public static function serializeParametersAsString($parameters, $defaultParameters = array())
    {
        $p = array();
        foreach ($parameters as $name => $v) {
            if (is_array($v)) {
                if (!count($v)) {
                    continue;
                }
                $v = '['.implode(',', $v).']';
            }
            if (isset($defaultParameters[$name]) && $defaultParameters[$name] === $v && $v !== true) {
                // don't write values that equals to default ones except for
                // true values else we could not known into the installer if
                // the absence of the parameter means the default value or
                // it if means false
                continue;
            }
            if ($v === true || $v === 'true') {
                $p[] = $name;
            } elseif ($v === false || $v === 'false') {
                if (isset($defaultParameters[$name]) && is_bool($defaultParameters[$name])) {
                    continue;
                }
                $p[] = $name.'=false';
            } else {
                $p[] = $name.'='.$v;
            }
        }

        foreach ($defaultParameters as $name => $v) {
            if ($v === true && !isset($parameters[$name])) {
                $p[] = $name;
            }
        }

        return implode(';', $p);
    }

    /**
     * Serialize parameters to be stores into an ini file.
     *
     * The result is an array with serialized value.
     *
     * @param array $parameters
     * @param array $defaultParameters
     *
     * @return array
     */
    public static function serializeParametersAsArray($parameters, $defaultParameters = array())
    {
        $p = array();
        foreach ($parameters as $name => $v) {
            if (is_array($v)) {
                if (!count($v)) {
                    continue;
                }
                $v = '['.implode(',', $v).']';
            }
            if (isset($defaultParameters[$name]) && $defaultParameters[$name] === $v) {
                // don't write values that equals to default ones
                continue;
            }
            $p[$name] = $v;
        }

        return $p;
    }
}
