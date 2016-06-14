<?php
/**
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Routing\UrlMapping;


class XmlEntryPoint {
    
    /**
     * @var \DOMElement
     */
    protected $ep;

    /**
     * @var XmlMapModifier
     */
    protected $map;

    function __construct(XmlMapModifier $map, \DOMElement $ep) {
        $this->ep = $ep;
        $this->map = $map;
    }

    public function setOptions($options) {
        $authorizedOptions = array('default','https','noentrypoint','optionalTrailingSlash');
        $this->setElementOptions($this->ep, $options, $authorizedOptions);
    }

    public function isDefault() {
        return ($this->ep->getAttribute('default') == 'true');
    }

    public function getName() {
        return $this->ep->getAttribute('name');
    }

    public function getType() {
        return $this->ep->getAttribute('type');
    }

    protected function setElementOptions($element, $options, $authorizedOptions) {
        if ($options && is_array($options)) {
            foreach($options as $opt=>$value) {
                if(!in_array($opt, $authorizedOptions)) {
                    continue;
                }
                if ($opt == 'actionoverride') {
                    $element->setAttribute($opt, $value); 
                } else if ($opt == 'default') {
                    if ($value) {
                        $this->map->setNewDefaultEntryPoint($this->getName(), $this->getType());
                    } else {
                        $element->removeAttribute($opt);
                    }
                } else if ($value) {
                    $element->setAttribute($opt, 'true');
                } else {
                    $element->removeAttribute($opt);
                }
            }
        }
    }

    /**
     * add an url action.
     *
     * It if already exists, and if $parameters or $statics or $options are not
     * null, existing parameters, statics or options will be changed.
     * @param string $pathinfo
     * @param string $module
     * @param string $action
     * @param array $parameters  key is name, value is array('type'=>'', 'regexp'=>'', 'escape'=>true/false)
     * @param array $statics   key is name, value is array('type'=>'', 'value'=>'')
     * @param array $options options are :
     *      default => true/(false)
     *      https => true/(false)
     *      noentrypoint => true/(false)
     *      actionoverride => list of method name
     *      optionalTrailingSlash => true/(false)
     */
    public function addUrlAction($pathinfo, $module, $action, $parameters=null,
                                 $statics=null, $options=null) {
        $url = $this->getUrlByModuleAction($module, $action);
        if (!$url) {
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            if ($action) {
                $url->setAttribute('action', $action);
            }
            $this->appendElement($this->ep, $url);
        }
        else {
            if ($parameters !== null && is_array($parameters)) {
                $list = $url->getElementsByTagName('param');
                foreach($list as $p) {
                    $url->removeChild($p);
                }
            }
            if ($statics !== null && is_array($statics)) {
                $list = $url->getElementsByTagName('static');
                foreach($list as $p) {
                    $url->removeChild($p);
                }
            }
        }
        $this->setElementOptions($url, $options, array('default','https','noentrypoint',
                                                       'actionoverride', 'optionalTrailingSlash'));

        // set parameters
        if ($parameters !== null && is_array($parameters)) {
            foreach($parameters as $name=>$attrs) {
                $param = $this->ep->ownerDocument->createElement('param');
                $param->setAttribute('name', $name);
                foreach(array('type','regexp','escape') as $attr) {
                    if (isset($attrs[$attr])) {
                        $param->setAttribute($attr, $attrs[$attr]);
                    }
                }
                $this->appendElement($url, $param, '            ');
            }
        }
        // set statics
        if ($statics !== null && is_array($statics)) {
            foreach($statics as $name=>$attrs) {
                $static = $this->ep->ownerDocument->createElement('static');
                $static->setAttribute('name', $name);
                $static->setAttribute('value', $attrs['value']);
                if (isset($attrs['type'])) {
                    $static->setAttribute('type', $attrs['type']);
                }
                $this->appendElement($url, $static, '            ');
            }
        }
    }

    /**
     * add an url controller
     *
     * the url matches any methods of a controller
     *
     * It if already exists, and if $options is not null,
     * existing options will be changed.
     * 
     * @param string $pathinfo
     * @param string $module
     * @param string $controller
     * @param array $options options are :
     *      default => true/(false)
     *      https => true/(false)
     *      noentrypoint => true/(false)
     */
    public function addUrlController($pathinfo, $module, $controller, $options=null) {

        $url = $this->getUrlByModuleController($module, $controller);
        if (!$url) {
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            $url->setAttribute('controller', $controller);
            $this->appendElement($this->ep, $url);
        }
        else {
            $url->setAttribute('pathinfo', $pathinfo);
        }

        $this->setElementOptions($url, $options, array('https','noentrypoint'));
    }

    /**
     * add an url module
     *
     * the entrypoint is dedicated to this module and url are automatic
     * 
     *
     * It if already exists, and if $options is not null,
     * existing options will be changed.
     * 
     * @param string $module
     * @param string $pathinfo
     * @param array $options options are :
     *      default => true/(false)
     *      https => true/(false)
     *      noentrypoint => true/(false)
     */
    public function addUrlModule($pathinfo, $module, $options=null) {

        $url = $this->getUrlByDedicatedModule($module);
        if (!$url) {
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            $this->appendElement($this->ep, $url);
        }
        else {
            $url->setAttribute('pathinfo', $pathinfo);
        }

        $this->setElementOptions($url, $options, array('default','https','noentrypoint'));
    }

    /**
     * add an url handler.
     *
     * It if already exists, and if $options is not null, existing options will
     * be changed.
     *
     * @param string $handler
     * @param string $pathinfo
     * @param string $module
     * @param string $action
     * @param array $options options are :
     *      default => true/(false)
     *      https => true/(false)
     *      noentrypoint => true/(false)
     *      actionoverride => list of method name
     */
    public function addUrlHandler($pathinfo, $handler, $module, $action='', $options = null) {
        $url = $this->getUrlByHandler($handler, $module);
        if (!$url) {
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('handler', $handler);
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            if ($action) {
                $url->setAttribute('action', $action);
            }
            $this->appendElement($this->ep, $url);
        }
        else {
            $url->setAttribute('pathinfo', $pathinfo);
        }

        $this->setElementOptions($url, $options, array('default','https','noentrypoint', 'actionoverride'));
    }

    /**
     * add an url handler.
     *
     * It if already exists, and if $options is not null, existing options will
     * be changed.
     * 
     * @param string $pathinfo
     * @param string $module
     * @param string $action
     * @param array $options options are :
     *      https => true/(false)
     *      noentrypoint => true/(false)
     */
    public function addUrlInclude($include, $module, $pathinfo, $options = null) {
        $url = $this->getUrlByInclude($include, $module);
        if (!$url) {
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('include', $include);
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            $this->appendElement($this->ep, $url);
        }
        else {
            $url->setAttribute('pathinfo', $pathinfo);
        }
        $this->setElementOptions($url, $options, array('https','noentrypoint'));
    }

    protected function getUrlByPathinfo($pathinfo) {
        $list = $this->ep->getElementsByTagName('url');
        foreach($list as $item) {
            if ($item->getAttribute('pathinfo') == $pathinfo) {
                return $item;
            }
        }
        return null;
    }

    protected function getUrlByDedicatedModule($module) {
        $list = $this->ep->getElementsByTagName('url');
        foreach($list as $item) {
            if ($item->getAttribute('module') == $module) {
                if ($item->getAttribute('action') == '' &&
                    $item->getAttribute('include') == '' &&
                    $item->getAttribute('handler') == '') {
                    return $item;
                }
            }
        }
        return null;
    }

    protected function getUrlByHandler($handler, $module) {
        $list = $this->ep->getElementsByTagName('url');
        foreach($list as $item) {
            if ($item->getAttribute('module') == $module &&
                $item->getAttribute('handler') == $handler) {
                return $item;
            }
        }
        return null;
    }

    protected function getUrlByInclude($include, $module) {
        $list = $this->ep->getElementsByTagName('url');
        foreach($list as $item) {
            if ($item->getAttribute('module') == $module &&
                $item->getAttribute('include') == $include) {
                return $item;
            }
        }
        return null;
    }

    protected function getUrlByModuleAction($module, $action) {
        $list = $this->ep->getElementsByTagName('url');
        foreach($list as $item) {
            if ($item->getAttribute('module') == $module &&
                $item->getAttribute('action') == $action) {
                return $item;
            }
        }
        return null;
    }

    protected function getUrlByModuleController($module, $controller) {
        $list = $this->ep->getElementsByTagName('url');
        foreach($list as $item) {
            if ($item->getAttribute('module') == $module &&
                $item->getAttribute('controller') == $controller) {
                return $item;
            }
        }
        return null;
    }

    protected function appendElement(\DOMElement $parent, \DOMElement $child, $indent='        ') {
        $doc = $parent->ownerDocument;
        if ($parent->lastChild && $parent->lastChild->nodeType == XML_TEXT_NODE) {
            $parent->lastChild->data = "\n".$indent;
        }
        else {
            $parent->appendChild($doc->createTextNode("\n".$indent));
        }
        $parent->appendChild($child);
        $parent->appendChild($doc->createTextNode("\n".substr($indent, 0, strlen($indent)-4)));
    }
}