<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2026 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping\MapEntry;

use Jelix\Routing\UrlMapping\XmlEntryPoint;

class ActionUrl extends AbstractEntry
{
    const PARAM_TYPE_STRING = 'string';
    const PARAM_TYPE_CHAR = 'char';
    const PARAM_TYPE_LETTER = 'letter';
    const PARAM_TYPE_NUMBER = 'number';
    const PARAM_TYPE_INT = 'int';
    const PARAM_TYPE_INTEGER = 'integer';
    const PARAM_TYPE_DIGIT = 'digit';
    const PARAM_TYPE_DATE = 'date';
    const PARAM_TYPE_YEAR = 'year';
    const PARAM_TYPE_MONTH = 'month';
    const PARAM_TYPE_DAY = 'day';
    const PARAM_TYPE_PATH = 'path';
    const PARAM_TYPE_LOCALE = 'locale';
    const PARAM_TYPE_LANG = 'lang';

    /**
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $actionOverride;

    /**
     * @var boolean
     */
    protected $optionalTrailingSlash = false;

    protected $staticParameters = array();
    protected $dynamicParameters = array();

    /**
     * @param string $action The action to which this url mapping entry is associated
     * @param string $pathInfo the url path info
     * @param array $options options of this url mapping entry. It may contains theses options:
     * - actionOverride: the action to override the action defined in the url mapping entry
     * - optionalTrailingSlash: if true, the url mapping entry will match urls with or without a trailing slash
     * - staticParameters: an array of static parameters.
     *   Ex array('<name>' => '<value>') or array('<name>' => ['value'=>'<value>', 'type'=> ActionUrl::PARAM_TYPE_*]).
     * - dynamicParameters: an array of dynamic parameters to add to the url
     *   Ex: array('<name>' => ['type'=> ActionUrl::PARAM_TYPE_*, 'escape'=> true/false])
     *   or array('<name>' => ['regexp'=> ActionUrl::PARAM_TYPE_*, 'escape'=> true/false])
     */
    public function __construct($action, $pathInfo, $options = array())
    {
        parent::__construct($pathInfo);
        $this->action = $action;

        if (isset($options['actionOverride'])) {
            $this->actionOverride = $options['actionOverride'];
        }
        if (isset($options['optionalTrailingSlash'])) {
            $this->optionalTrailingSlash = $options['optionalTrailingSlash'];
        }
        if (isset($options['staticParameters'])) {
            foreach($options['staticParameters'] as $name => $value)
            {
                if(is_array($value))
                {
                    $this->staticParameters[$name] = array($value['value'], $value['type'] ?? '');
                }
                else
                {
                    $this->staticParameters[$name] = array($value, '');
                }
            }
        }
        if (isset($options['dynamicParameters'])) {
            $this->dynamicParameters = $options['dynamicParameters'];
            foreach($options['dynamicParameters'] as $name => $attributes)
            {
                if (!isset($attributes['escape'])) {
                    $attributes['escape'] = false;
                }
                $this->dynamicParameters[$name] = $attributes;
            }
        }

    }

    public function setActionOverride($actionOverride)
    {
        $this->actionOverride = $actionOverride;
    }

    /**
     * @param string $name
     * @param string $type one of PARAM_TYPE_* const
     * @param boolean $escapeValueInUrl
     * @return void
     */
    public function setDynamicParameterType($name, $type, $escapeValueInUrl = false)
    {
        $this->dynamicParameters[$name] = array(
            'type' => $type,
            'escape' => $escapeValueInUrl
        );
    }

    public function setDynamicParameterRegexp($name, $regexp, $escapeValueInUrl = false)
    {
        $this->dynamicParameters[$name] = array(
            'regexp' => $regexp,
            'escape' => $escapeValueInUrl
        );

    }

    /**
     * @param string $name
     * @param string $value
     * @param string $type PARAM_TYPE_LOCALE or PARAM_TYPE_LANG or PARAM_TYPE_STRING
     * @return void
     */
    public function addStaticParameter($name, $value, $type='')
    {
        $this->staticParameters[$name] = array($value, $type);
    }

    public function setOptionalTrailingSlash()
    {
        $this->optionalTrailingSlash = true;
    }

    public function hasOptionalTrailingSlash()
    {
        return $this->optionalTrailingSlash;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getActionOverride()
    {
        return $this->actionOverride;
    }

    /**
     * @return array
     */
    public function getStaticParameters(): array
    {
        return $this->staticParameters;
    }

    /**
     * @return array
     */
    public function getDynamicParameters(): array
    {
        return $this->dynamicParameters;
    }

    /**
     * {@inheritDoc}
     */
    public function addToEntryPoint(XmlEntryPoint $ep, $module)
    {
        $pathInfo = ($this->pathInfo ?: '/'.$module);
        $options = [];
        if ($this->isDefault()) {
            $options['default'] = true;
        }
        if ($this->isForHttpsOnly()) {
            $options['https'] = true;
        }
        if ($this->hasOptionalTrailingSlash()) {
            $options['optionalTrailingSlash'] = true;
        }
        if ($this->actionOverride) {
            $options['actionoverride'] = $this->actionOverride;
        }

        $ep->addUrlAction($pathInfo, $module, $this->action,
            $this->dynamicParameters,
            $this->staticParameters,
            $options);
    }

    /**
     * {@inheritDoc}
     */
    public function removeFromEntryPoint(XmlEntryPoint $ep, $module)
    {
        $ep->removeUrlAction($module, $this->action);
    }
}
