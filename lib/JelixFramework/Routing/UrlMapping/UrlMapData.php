<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Thibault Piront (nuKs)
 *
 * @copyright   2005-2016 Laurent Jouanneau
 * @copyright   2007 Thibault Piront
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

/**
 * Contain informations of an url, readed from the map file.
 */
class UrlMapData
{
    public $entryPoint = '';
    public $entryPointUrl = '';
    public $isHttps = false;
    public $isDefault = false;
    public $action = '';
    public $module = '';
    /**
     * @var bool|string[]
     */
    public $actionOverride = false;
    public $requestType = '';
    public $statics = array();
    public $params = array();
    public $escapes = array();

    /**
     * @param string $rt      entrypoint type
     * @param string $ep      entrypoint name
     * @param bool   $isHttps indicate if https is requiered
     */
    public function __construct($rt, $ep, $isHttps)
    {
        $this->requestType = $rt;
        $this->entryPoint = $this->entryPointUrl = $ep;
        $this->isHttps = $isHttps;
    }

    public function getFullSel()
    {
        if ($this->action) {
            $act = $this->action;
            if (substr($act, -1) == ':') { // this is a rest action
                // we should add index because jSelectorAct resolve a "ctrl:" as "ctrl:index"
                // and then create the corresponding selector so url create infos will be found
                $act .= 'index';
            }
        } else {
            $act = '*';
        }

        return $this->module.'~'.$act.'@'.$this->requestType;
    }

    public function setAction($action)
    {
        if (strpos($action, ':') === false) {
            $this->action = 'default:'.$action;
        } else {
            $this->action = $action;
        }
    }

    public function setActionOverride($actionoverride)
    {
        $this->actionOverride = preg_split('/[\\s,]+/', $actionoverride);
        foreach ($this->actionOverride as &$each) {
            if (strpos($each, ':') === false) {
                $each = 'default:'.$each;
            }
        }
    }
}
