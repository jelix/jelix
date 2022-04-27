<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

class XmlEntryPoint
{
    /**
     * @var \DOMElement
     */
    protected $ep;

    /**
     * @var XmlMapModifier
     */
    protected $map;

    public function __construct(XmlMapModifier $map, \DOMElement $ep)
    {
        $this->ep = $ep;
        $this->map = $map;
    }

    public function setOptions($options)
    {
        $authorizedOptions = array('default', 'https', 'noentrypoint', 'optionalTrailingSlash');
        $this->setElementOptions($this->ep, $options, $authorizedOptions);
    }

    public function isDefault()
    {
        return $this->ep->getAttribute('default') == 'true';
    }

    public function getName()
    {
        return $this->ep->getAttribute('name');
    }

    public function getType()
    {
        return $this->ep->getAttribute('type');
    }

    public function getMapModifier()
    {
        return $this->map;
    }

    /**
     * @return \DOMElement
     */
    public function getDomElement()
    {
        return $this->ep;
    }

    protected function setElementOptions($element, $options, $authorizedOptions)
    {
        if ($options && is_array($options)) {
            foreach ($options as $opt => $value) {
                if (!in_array($opt, $authorizedOptions)) {
                    continue;
                }
                if ($opt == 'actionoverride') {
                    $element->setAttribute($opt, $value);
                } elseif ($opt == 'default' && $element->localName != 'url') {
                    if ($value) {
                        $this->map->setNewDefaultEntryPoint($this->getName(), $this->getType());
                    } else {
                        $element->removeAttribute($opt);
                    }
                } elseif ($value) {
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
     *
     * @param string $pathinfo
     * @param string $module
     * @param string $action
     * @param array  $parameters key is name, value is array('type'=>'', 'regexp'=>'', 'escape'=>true/false)
     * @param array  $statics    key is name, value is array('type'=>'', 'value'=>'')
     * @param array  $options    options are :
     *                           default => true/(false)
     *                           https => true/(false)
     *                           noentrypoint => true/(false)
     *                           actionoverride => list of method name
     *                           optionalTrailingSlash => true/(false)
     */
    public function addUrlAction(
        $pathinfo,
        $module,
        $action,
        $parameters = null,
        $statics = null,
        $options = null
    ) {
        $url = $this->getUrlByModuleAction($module, $action);
        $urlPathInfo = $this->getUrlByPathinfo($pathinfo);
        if (!$url) {
            if ($urlPathInfo) {
                $this->removeElement($urlPathInfo);
            }
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            if ($action) {
                $url->setAttribute('action', $action);
            }
            $this->appendElement($this->ep, $url);
        } else {
            if ($urlPathInfo && $url !== $urlPathInfo) {
                $this->removeElement($urlPathInfo);
            }
            if ($parameters !== null && is_array($parameters)) {
                $list = $url->getElementsByTagName('param');
                $listP = array();
                foreach ($list as $p) {
                    // we don't remove yet, as $list is modified at each remove
                    $listP[] = $p;
                }
                foreach ($listP as $p) {
                    $this->removeElement($p);
                }
            }
            if ($statics !== null && is_array($statics)) {
                $list = $url->getElementsByTagName('static');
                $listP = array();
                foreach ($list as $p) {
                    // we don't remove yet, as $list is modified at each remove
                    $listP[] = $p;
                }
                foreach ($listP as $p) {
                    $this->removeElement($p);
                }
            }
        }
        $this->setElementOptions($url, $options, array('default', 'https', 'noentrypoint',
            'actionoverride', 'optionalTrailingSlash', ));

        $this->setUrlParametersStatics($url, $parameters, $statics);
    }

    protected function setUrlParametersStatics(
        \DOMElement $url,
        $parameters = null,
        $statics = null
    ) {

        // set parameters
        if ($parameters !== null && is_array($parameters)) {
            foreach ($parameters as $name => $attrs) {
                $param = $this->ep->ownerDocument->createElement('param');
                $param->setAttribute('name', $name);
                foreach (array('type', 'regexp', 'escape') as $attr) {
                    if (isset($attrs[$attr])) {
                        $param->setAttribute($attr, $attrs[$attr]);
                    }
                }
                $this->appendElement($url, $param, '            ');
            }
        }
        // set statics
        if ($statics !== null && is_array($statics)) {
            foreach ($statics as $name => $attrs) {
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
     * add an url controller.
     *
     * the url matches any methods of a controller
     *
     * It if already exists, and if $options is not null,
     * existing options will be changed.
     *
     * @param string $pathinfo
     * @param string $module
     * @param string $controller
     * @param array  $options    options are :
     *                           default => true/(false)
     *                           https => true/(false)
     *                           noentrypoint => true/(false)
     */
    public function addUrlController($pathinfo, $module, $controller, $options = null)
    {
        $url = $this->getUrlByModuleController($module, $controller);
        $urlPathInfo = $this->getUrlByPathinfo($pathinfo);
        if (!$url) {
            if ($urlPathInfo) {
                $this->removeElement($urlPathInfo);
            }
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            $url->setAttribute('controller', $controller);
            $this->appendElement($this->ep, $url);
        } else {
            if ($urlPathInfo && $url !== $urlPathInfo) {
                $this->removeElement($urlPathInfo);
            }
            $url->setAttribute('pathinfo', $pathinfo);
        }

        $this->setElementOptions($url, $options, array('https', 'noentrypoint'));
    }

    /**
     * add an url module.
     *
     * the entrypoint is dedicated to this module and url are automatic
     *
     *
     * It if already exists, and if $options is not null,
     * existing options will be changed.
     *
     * @param string $module
     * @param string $pathinfo
     * @param array  $options  options are :
     *                         default => true/(false)
     *                         https => true/(false)
     *                         noentrypoint => true/(false)
     */
    public function addUrlModule($pathinfo, $module, $options = null)
    {
        $url = $this->getUrlByDedicatedModule($module);
        if (!$url) {
            $url = $this->ep->ownerDocument->createElement('url');
            if ($pathinfo) {
                $urlPathInfo = $this->getUrlByPathinfo($pathinfo);
                if ($urlPathInfo) {
                    $this->removeElement($urlPathInfo);
                }
                $url->setAttribute('pathinfo', $pathinfo);
            }
            $url->setAttribute('module', $module);
            $this->appendElement($this->ep, $url);
            $this->map->removeUrlModuleInOtherEntryPoint($module, $this);
        } elseif ($pathinfo) {
            $urlPathInfo = $this->getUrlByPathinfo($pathinfo);
            if ($urlPathInfo && $urlPathInfo !== $url) {
                $this->removeElement($urlPathInfo);
            }
            $url->setAttribute('pathinfo', $pathinfo);
        } else {
            $url->removeAttribute('pathinfo');
        }

        $this->setElementOptions($url, $options, array('default', 'https', 'noentrypoint'));
    }

    public function removeUrlModule($module)
    {
        $url = $this->getUrlByDedicatedModule($module);
        if ($url) {
            $this->removeElement($url);
        }
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
     * @param array  $options  options are :
     *                         default => true/(false)
     *                         https => true/(false)
     *                         noentrypoint => true/(false)
     *                         actionoverride => list of method name
     */
    public function addUrlHandler($pathinfo, $module, $handler, $action = '', $options = null)
    {
        $url = $this->getUrlByHandler($handler, $module);
        $urlPathInfo = $this->getUrlByPathinfo($pathinfo);
        if (!$url) {
            if ($urlPathInfo) {
                $this->removeElement($urlPathInfo);
            }
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('handler', $handler);
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            if ($action) {
                $url->setAttribute('action', $action);
            }
            $this->appendElement($this->ep, $url);
        } else {
            if ($urlPathInfo && $urlPathInfo !== $url) {
                $this->removeElement($urlPathInfo);
            }
            $url->setAttribute('pathinfo', $pathinfo);
        }

        $this->setElementOptions($url, $options, array('default', 'https', 'noentrypoint', 'actionoverride'));
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
     * @param array  $options  options are :
     *                         https => true/(false)
     *                         noentrypoint => true/(false)
     * @param mixed  $include
     */
    public function addUrlInclude($pathinfo, $module, $include, $options = null)
    {
        $url = $this->getUrlByInclude($include, $module);
        $urlPathInfo = $this->getUrlByPathinfo($pathinfo);
        if (!$url) {
            if ($urlPathInfo) {
                $this->removeElement($urlPathInfo);
            }
            $url = $this->ep->ownerDocument->createElement('url');
            $url->setAttribute('include', $include);
            $url->setAttribute('pathinfo', $pathinfo);
            $url->setAttribute('module', $module);
            $this->appendElement($this->ep, $url);
        } else {
            if ($urlPathInfo && $urlPathInfo !== $url) {
                $this->removeElement($urlPathInfo);
            }
            $url->setAttribute('pathinfo', $pathinfo);
        }
        $this->setElementOptions($url, $options, array('https', 'noentrypoint'));
    }

    /**
     * @param string $pathinfo
     *
     * @return null|\DOMElement
     */
    protected function getUrlByPathinfo($pathinfo)
    {
        $list = $this->ep->getElementsByTagName('url');
        /** @var \DOMElement $item */
        foreach ($list as $item) {
            if ($item->getAttribute('pathinfo') == $pathinfo) {
                return $item;
            }
        }

        return null;
    }

    public function hasUrlByPathinfo($pathinfo)
    {
        return $this->getUrlByPathinfo($pathinfo) !== null;
    }

    /**
     * @param string $module
     *
     * @return null|\DOMElement
     */
    protected function getUrlByDedicatedModule($module)
    {
        /** @var \DOMNodeList $list */
        $list = $this->ep->getElementsByTagName('url');
        /** @var \DOMElement $item */
        foreach ($list as $item) {
            if ($item->getAttribute('module') == $module) {
                if ($item->getAttribute('action') == ''
                    && $item->getAttribute('include') == ''
                    && $item->getAttribute('handler') == '') {
                    return $item;
                }
            }
        }

        return null;
    }

    public function hasUrlByDedicatedModule($module)
    {
        return $this->getUrlByDedicatedModule($module) !== null;
    }

    /**
     * @param string $handler
     * @param string $module
     *
     * @return null|\DOMElement
     */
    protected function getUrlByHandler($handler, $module)
    {
        $list = $this->ep->getElementsByTagName('url');
        /** @var \DOMElement $item */
        foreach ($list as $item) {
            if ($item->getAttribute('module') == $module
                && $item->getAttribute('handler') == $handler) {
                return $item;
            }
        }

        return null;
    }

    public function hasUrlByHandler($handler, $module)
    {
        return $this->getUrlByHandler($handler, $module) !== null;
    }

    /**
     * @param string $include
     * @param string $module
     *
     * @return null|\DOMElement
     */
    protected function getUrlByInclude($include, $module)
    {
        $list = $this->ep->getElementsByTagName('url');
        /** @var \DOMElement $item */
        foreach ($list as $item) {
            if ($item->getAttribute('module') == $module
                && $item->getAttribute('include') == $include) {
                return $item;
            }
        }

        return null;
    }

    public function hasUrlByInclude($include, $module)
    {
        return $this->getUrlByInclude($include, $module) !== null;
    }

    /**
     * @param string $module
     * @param string $action
     *
     * @return null|\DOMElement
     */
    protected function getUrlByModuleAction($module, $action)
    {
        $list = $this->ep->getElementsByTagName('url');
        /** @var \DOMElement $item */
        foreach ($list as $item) {
            if ($item->getAttribute('module') == $module
                && $item->getAttribute('action') == $action) {
                return $item;
            }
        }

        return null;
    }

    public function hasUrlByModuleAction($module, $action)
    {
        return $this->getUrlByModuleAction($module, $action) !== null;
    }

    /**
     * @param string $module
     * @param string $controller
     *
     * @return null|\DOMElement
     */
    protected function getUrlByModuleController($module, $controller)
    {
        $list = $this->ep->getElementsByTagName('url');
        /** @var \DOMElement $item */
        foreach ($list as $item) {
            if ($item->getAttribute('module') == $module
                && $item->getAttribute('controller') == $controller) {
                return $item;
            }
        }

        return null;
    }

    public function hasUrlByModuleController($module, $controller)
    {
        return $this->getUrlByModuleController($module, $controller) !== null;
    }

    /**
     * @param string $indent
     */
    protected function appendElement(\DOMElement $parent, \DOMElement $child, $indent = '        ')
    {
        $doc = $parent->ownerDocument;
        if ($parent->lastChild && $parent->lastChild->nodeType == XML_TEXT_NODE) {
            $parent->lastChild->data = "\n".$indent;
        } else {
            $parent->appendChild($doc->createTextNode("\n".$indent));
        }
        $parent->appendChild($child);
        $parent->appendChild($doc->createTextNode("\n".substr($indent, 0, strlen($indent) - 4)));
    }

    protected function removeElement(\DOMElement $child)
    {
        $parent = $child->parentNode;
        if ($child->previousSibling && $child->previousSibling->nodeType == XML_TEXT_NODE) {
            // remove indentation
            $parent->removeChild($child->previousSibling);
        }
        $parent->removeChild($child);
    }
}
