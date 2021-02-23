<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2005-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

use Jelix\Core\App;
use Jelix\Locale\Locale;

/**
 * an url engine to parse,analyse and create significant url
 * it needs an urls.xml file in the app/system directory (see documentation).
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2016 Laurent Jouanneau
 */
class UrlActionMapper
{
    /**
     * data to create significant url.
     *
     * @var array
     */
    protected $dataCreateUrl;

    /**
     * data to parse and anaylise significant url, and to determine action, module etc..
     *
     * @var array
     */
    protected $dataParseUrl;

    const ESCAPE_URLENCODE = 0;
    const ESCAPE_SLASH = 1;
    const ESCAPE_NON_ASCII = 2;
    const ESCAPE_LANG = 4;
    const ESCAPE_LOCALE = 8;

    protected $entryPointTypeHavingActionInBody = array('xmlrpc', 'jsonrpc', 'soap');

    protected $xmlfileSelector;

    /**
     * @param MapperConfig
     */
    protected $config;

    public function __construct(MapperConfig $config)
    {
        $this->config = $config;
        $this->xmlfileSelector = new SelectorUrlXmlMap($config->mapFile, $config->localMapFile);
        \Jelix\Core\Includer::inc($this->xmlfileSelector, true);
        $this->dataCreateUrl = &$GLOBALS['SIGNIFICANT_CREATEURL'];
    }

    public function __clone()
    {
        $this->config = clone $this->config;
    }

    /**
     * @return MapperConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Parse a url from the request.
     *
     * @param array $params url parameters
     *
     * @return \jUrlAction
     *
     * @since 1.1
     */
    public function parseFromRequest(\jRequest $request, $params)
    {
        if ($this->config->enableParser) {
            $file = App::tempPath('compiled/urlsig/'.$this->xmlfileSelector->file.'.'.$this->config->entryPointName.'.entrypoint.php');
            if (file_exists($file)) {
                require $file;
                $this->dataParseUrl = &$GLOBALS['SIGNIFICANT_PARSEURL'][$this->config->entryPointName];
            }

            $isHttps = ($request->getProtocol() == 'https://');

            return $this->_parse($request->urlScript, $request->urlPathInfo, $params, $isHttps);
        }

        $urlact = new \jUrlAction($params);

        return $urlact;
    }

    /**
     * Parse some url components.
     *
     * @param string $scriptNamePath /path/index.php
     * @param string $pathinfo       the path info part of the url (part between script name and query)
     * @param array  $params         url parameters (query part e.g. $_REQUEST)
     *
     * @return \jUrlAction
     */
    public function parse($scriptNamePath, $pathinfo, $params)
    {
        if ($this->config->enableParser) {
            if (strpos($scriptNamePath, $this->config->basePath) === 0) {
                $snp = substr($scriptNamePath, strlen($this->config->basePath));
            } else {
                $snp = $scriptNamePath;
            }
            $pos = strrpos($snp, '.php');
            if ($pos !== false) {
                $snp = substr($snp, 0, $pos);
            }
            $snp = rawurlencode($snp);
            $file = App::tempPath('compiled/urlsig/'.$this->xmlfileSelector->file.'.'.$snp.'.entrypoint.php');
            if (file_exists($file)) {
                require $file;
                $this->dataParseUrl = &$GLOBALS['SIGNIFICANT_PARSEURL'][$snp];

                return $this->_parse($scriptNamePath, $pathinfo, $params, false);
            }
        }
        $urlact = new \jUrlAction($params);

        return $urlact;
    }

    /**
     * @param string $scriptNamePath /path/index.php
     * @param string $pathinfo       the path info part of the url (part between script name and query)
     * @param array  $params         url parameters (query part e.g. $_REQUEST)
     * @param bool   $isHttps        says if the given url is asked with https or not
     *
     * @return \jUrlAction
     */
    protected function _parse($scriptNamePath, $pathinfo, $params, $isHttps)
    {
        if ($pathinfo == '') {
            $pathinfo = '/';
        }
        $urlact = null;
        $url = new \jUrl($scriptNamePath, $params, $pathinfo);
        $needHttps = false;

        $basicPathInfoConf = null;

        foreach ($this->dataParseUrl as $k => $infoparsing) {
            // the first element contains some informations about the entry point
            if ($k == 0) {
                $basicPathInfoConf = $infoparsing;

                continue;
            }

            if (count($infoparsing) < 7) {
                // an handler will parse the request URI
                $url2 = clone $url;
                $urlact = $this->parseWithHandler($infoparsing, $url2);
                if ($urlact) {
                    break;
                }
            } elseif (preg_match($infoparsing[2], $pathinfo, $matches)) {

                // the pathinfo match the regexp, we found the informations
                // to extract parameters and module/action
                $urlact = $this->parseGetParams($infoparsing, $url, $matches);

                break;
            }
        }

        if (!$urlact) {
            // we didn't find a pathinfo that match the url's one
            // let's parse the pathinfo as /module/controller/method
            $urlact = $this->parseBasicPathinfo($basicPathInfoConf, $url);
        }

        if ($urlact) {
            // the action corresponding to the url has been found
            if (!($this->config->checkHttpsOnParsing && $urlact->needsHttps && !$isHttps)) {
                return $urlact;
            }
            // the url is declared for HTTPS, but the request does not come from HTTPS
            // -> 404 not found
        }

        // we display the 404 page.
        try {
            $urlact = \jUrl::get($this->config->notFoundAct, array(), \jUrl::JURLACTION);
        } catch (\Exception $e) {
            $urlact = new \jUrlAction(array('module' => 'jelix', 'action' => 'error:notfound'));
        }

        return $urlact;
    }

    /**
     * Create a jurl object with the given action data.
     *
     * @param \jUrlAction $url information about the action
     *
     * @return \jUrl the url correspondant to the action
     *
     * @author      Laurent Jouanneau
     * @copyright   2005 CopixTeam, 2005-2006 Laurent Jouanneau
     *   very few lines of code are copyrighted by CopixTeam, written by Laurent Jouanneau
     *   and released under GNU Lesser General Public Licence,
     *   in an experimental version of Copix Framework v2.3dev20050901,
     *   http://www.copix.org.
     */
    public function create(\jUrlAction $urlact)
    {
        $url = new \jUrl('', $urlact->params, '');

        // retrieve informations corresponding to the action
        // warning: it may delete module and action parameter from $url
        $urlinfo = $this->getUrlBuilderInfo($urlact, $url);

        // at this step, we have informations to build the url

        // setup script name
        $url->scriptName = App::urlBasePath().$urlinfo[1];
        if ($urlinfo[2]) {
            $url->scriptName = App::router()->request->getServerURI(true).$url->scriptName;
        }

        if ($urlinfo[1] && $this->config->extensionNeeded) {
            $url->scriptName .= '.php';
        }

        // for some request types, parameters aren't in the url
        // so we remove them
        // it's a bit dirty to do that hardcoded here, but it would be a pain
        // to load the request class to check whether we can remove or not
        if (in_array($urlact->requestType, $this->entryPointTypeHavingActionInBody)) {
            $url->clearParam();

            return $url;
        }

        if ($urlinfo[0] == 0) {
            $this->buildWithHandler($urlact, $url, $urlinfo);
        } elseif ($urlinfo[0] == 1) {
            $this->buildWithSpecificPathinfo($urlact, $url, $urlinfo);
        } elseif ($urlinfo[0] == 3) {
            $this->buildForDedicatedModule($urlact, $url, $urlinfo);
        } elseif ($urlinfo[0] == 5) {
            $this->buildForWholeController($urlact, $url, $urlinfo);
        } elseif ($urlinfo[0] == 2) {
            $url->pathInfo = $this->simplifyDefaultAction(
                '/'.$urlact->getParam('module', App::getCurrentModule()),
                $urlact->getParam('action')
            );
            $url->delParam('module');
            $url->delParam('action');
        }

        return $url;
    }

    /**
     * search informations allowing to build the url corresponding to the
     * given module/action.
     *
     * @return array the informations. It may be:
     *               - array(0,'entrypoint', https true/false, 'handler selector', 'basepathinfo')
     *               - array(1,'entrypoint', https true/false,
     *               array('year','month',), // list of dynamic values included in the url
     *               array(true, false..), // list of integers which indicates for each
     *               // dynamic value: 0: urlencode, 1:urlencode except '/', 2:escape
     *               "/news/%1/%2/", // the url
     *               true/false, // false : this is a secondary action
     *               array('bla'=>'whatIWant' ) // list of static values
     *               )
     *               - array(2,'entrypoint', https true/false), // for the patterns "@request"
     *               - array(3,'entrypoint', https true/false, $defaultmodule true/false, 'pathinfobase'), // for the patterns "module~@request"
     *               - array(4, array(1,...), array(1,...)...)
     *               - array(5, 'entrypoint', https true/false,)
     */
    protected function getUrlBuilderInfo(\jUrlAction $urlact, \jUrl $url)
    {
        $module = $url->getParam('module', App::getCurrentModule());
        $action = $url->getParam('action');
        $urlinfo = null;

        // let's try to retrieve informations corresponding
        // to the given action. this informations will allow us to build
        // the url
        $id = $module.'~'.$action.'@'.$urlact->requestType;
        if (isset($this->dataCreateUrl[$id])) {
            $urlinfo = $this->dataCreateUrl[$id];
            $url->delParam('module');
            $url->delParam('action');
        } else {
            list($ctrl, $method) = explode(':', $action);
            $id = $module.'~'.$ctrl.':*@'.$urlact->requestType;
            if (isset($this->dataCreateUrl[$id])) {
                $urlinfo = $this->dataCreateUrl[$id];
                $url->delParam('module');
            } else {
                $id = $module.'~*@'.$urlact->requestType;
                if (isset($this->dataCreateUrl[$id])) {
                    $urlinfo = $this->dataCreateUrl[$id];
                    if ($urlinfo[0] != 3 || $urlinfo[3] === true) {
                        $url->delParam('module');
                    }
                } else {
                    $id = '@'.$urlact->requestType;
                    if (isset($this->dataCreateUrl[$id])) {
                        $urlinfo = $this->dataCreateUrl[$id];
                    } else {
                        throw new \Exception("URL engine doesn't find corresponding url to this action: ".$module.'~'.$action.'@'.$urlact->requestType);
                    }
                }
            }
        }

        if ($urlinfo[0] == 4) {
            // an action is mapped to several urls
            // so it isn't finished. Let's find building information
            // into the array
            $l = count($urlinfo);
            $urlinfofound = null;
            for ($i = 1; $i < $l; ++$i) {
                $ok = true;
                // verify that given static parameters of the action correspond
                // to those defined for this url
                foreach ($urlinfo[$i][7] as $n => $v) {
                    // specialStatic are static values for which the url engine
                    // can compare not only with a given url parameter value, but
                    // also with a value stored some where (typically, a configuration value)
                    $specialStatic = (!empty($v) && $v[0] == '$');
                    $paramStatic = $url->getParam($n, null);
                    if ($specialStatic) { // special statique value
                        $typePS = $v[1];
                        $v = substr($v, 2);
                        if ($typePS == 'l') {
                            if ($paramStatic === null) {
                                $paramStatic = Locale::getCurrentLang();
                            } elseif (preg_match('/^(\w{2,3})_\w{2,3}$/', $paramStatic, $m)) { // if the value is a locale instead of lang, translate it
                                $paramStatic = $m[1];
                            }
                        } elseif ($typePS == 'L') {
                            if ($paramStatic === null) {
                                $paramStatic = App::config()->locale;
                            } elseif (preg_match('/^\w{2,3}$/', $paramStatic, $m)) { // if the value is a lang instead of locale, translate it
                                $paramStatic = Locale::langToLocale($paramStatic);
                            }
                        }
                    }

                    if ($paramStatic != $v) {
                        $ok = false;

                        break;
                    }
                }
                if ($ok) {
                    // static parameters correspond: we found our informations
                    $urlinfofound = $urlinfo[$i];

                    break;
                }
            }
            if ($urlinfofound !== null) {
                $urlinfo = $urlinfofound;
            } else {
                $urlinfo = $urlinfo[1];
            }
        }

        return $urlinfo;
    }

    /**
     * @param array $urlinfo
     *                       array(0,
     *                       'entrypoint',
     *                       boolean https true/false,
     *                       'handler selector',
     *                       'basepathinfo')
     */
    protected function buildWithHandler(\jUrlAction $urlact, \jUrl $url, $urlinfo)
    {
        $s = new SelectorUrlHandler($urlinfo[3]);
        $c = $s->resource.'UrlsHandler';
        $handler = new $c();
        $handler->create($urlact, $url);
        if ($urlinfo[4] != '') {
            $url->pathInfo = $urlinfo[4].$url->pathInfo;
            if ($url->pathInfo == '/') {
                $url->pathInfo = '';
            }
        }
    }

    /**
     * @param array $urlinfo
     *                       array(1,'entrypoint', https true/false,
     *                       array('year','month',), // list of dynamic values included in the url
     *                       array(true, false..), // list of integers which indicates for each
     *                       // dynamic value: 0: urlencode, 1:urlencode except '/', 2:escape
     *                       "/news/%1/%2/", // the url
     *                       true/false, // false : this is a secondary action
     *                       array('bla'=>'whatIWant' ) // list of static values
     *                       )
     */
    protected function buildWithSpecificPathinfo(\jUrlAction $urlact, \jUrl $url, $urlinfo)
    {
        $pi = $urlinfo[5];
        foreach ($urlinfo[3] as $k => $param) {
            $escape = $urlinfo[4][$k];
            $value = $url->getParam($param, '');
            if ($escape & self::ESCAPE_NON_ASCII) {
                $value = \jUrl::escape($value, true);
            } elseif ($escape & self::ESCAPE_SLASH) {
                $value = str_replace('%2F', '/', urlencode($value));
            } elseif ($escape & self::ESCAPE_LANG) {
                if ($value == '') {
                    $value = Locale::getCurrentLang();
                } elseif (preg_match('/^(\w{2,3})_\w{2,3}$/', $value, $m)) {
                    $value = $m[1];
                }
            } elseif ($escape & self::ESCAPE_LOCALE) {
                if ($value == '') {
                    $value = App::config()->locale;
                } elseif (preg_match('/^\w{2,3}$/', $value, $m)) {
                    $value = Locale::langToLocale($value);
                }
            } else {
                $value = urlencode($value);
            }
            $pi = str_replace(':'.$param, $value, $pi);
            $url->delParam($param);
        }
        $url->pathInfo = ($pi != '/' ? $pi : '');
        if ($urlinfo[6]) {
            $url->setParam('action', $urlact->getParam('action'));
        }
        // removed parameters corresponding to static values
        foreach ($urlinfo[7] as $name => $value) {
            $url->delParam($name);
        }
    }

    /**
     * for the patterns "module~@request".
     *
     * @param array $urlinfo
     *                       array(3, 'entrypoint',
     *                       boolean https true/false,
     *                       boolean defaultmodule true/false,
     *                       'pathinfobase'),
     */
    protected function buildForDedicatedModule(\jUrlAction $urlact, \jUrl $url, $urlinfo)
    {
        $module = $urlact->getParam('module');
        $action = $urlact->getParam('action');
        if ($urlinfo[3]) { // if default module
            if ($action != 'default:index') {
                $url->pathInfo = $this->simplifyDefaultAction(
                    '/'.$module,
                    $action
                );
            }
        } else {
            $url->pathInfo = $this->simplifyDefaultAction(
                ($urlinfo[4] ?: '/'.$module),
                $action
            );
        }
        $url->delParam('module');
        $url->delParam('action');
    }

    protected function simplifyDefaultAction($pathInfo, $action)
    {
        if ($action != 'default:index') {
            $act = explode(':', $action);
            $pathInfo .= '/'.$act[0];
            if ($act[1] != 'index') {
                $pathInfo .= '/'.$act[1];
            }
        }

        return $pathInfo;
    }

    /**
     * for the patterns "module~ctrl:*@request".
     *
     * @param array $urlinfo
     *                       array(5, 'entrypoint',
     *                       boolean https true/false,
     *                       'pathinfobase'),
     */
    protected function buildForWholeController(\jUrlAction $urlact, \jUrl $url, $urlinfo)
    {
        list($ctrl, $method) = explode(':', $urlact->getParam('action'));
        $url->pathInfo = $urlinfo[3];
        if ($method != 'index') {
            $url->pathInfo .= '/'.$method;
        }
        $url->delParam('module');
        $url->delParam('action');
    }

    /**
     * call an handler to parse the url.
     *
     * @param mixed $infoparsing
     *
     * @return \jUrlAction or null if the handler does not accept the url
     */
    protected function parseWithHandler($infoparsing, \jUrl $url)
    {
        list($module, $action, $reg, $selectorHandler,
                    $secondariesActions, $needsHttps) = $infoparsing;
        if ($reg != '') {
            // if the path info match the regexp, we have the right handler
            if (preg_match($reg, $url->pathInfo, $m)) {
                $url->pathInfo = isset($m[1]) ? $m[1] : '/';
            } else {
                return null;
            }
        }

        // load the handler
        $s = new SelectorUrlHandler($selectorHandler);
        include_once $s->getPath();
        $c = $s->className.'UrlsHandler';
        $handler = new $c();
        $params = $url->params;
        $url->params['module'] = $module;

        // if the action parameter exists in the current url
        // and if it is one of secondaries actions, then we keep it
        // else we take the action indicated in the url mapping
        if ($secondariesActions && isset($params['action'])) {
            if (strpos($params['action'], ':') === false) {
                $params['action'] = 'default:'.$params['action'];
            }
            if (in_array($params['action'], $secondariesActions)) {
                // there is a secondary action in parameters, let's use it.
                $url->params['action'] = $params['action'];
            } else {
                $url->params['action'] = $action;
            }
        } else {
            $url->params['action'] = $action;
        }
        // call the url handler
        $urlact = $handler->parse($url);
        if ($urlact) {
            $urlact->needsHttps = $needsHttps;
        }

        return $urlact;
    }

    /**
     * extract parameters for the action from the path info.
     *
     * @params array $infoparsing  we have this array
     *                   array(
     *                   0=>'module',
     *                   1=>'action',
     *                   2=>'regexp_pathinfo',
     *                   3=>array('year','month'), // list of dynamic value included in the url,
     *                                         // alphabetical ascendant order
     *                   4=>array(0, 1..), // list of integer which indicates for each
     *                                   // dynamic value: 0: urlencode, 1:urlencode except '/', 2:escape, 4: lang, 8: locale
     *
     *                   5=>array('bla'=>'whatIWant' ), // list of static values
     *                   6=>false or array('secondaries','actions')
     *                   7=>true/false  true if https is needed
     * @params array $matches  result of the match with the regexp corresponding to the url
     *
     * @param mixed $infoparsing
     * @param mixed $matches
     *
     * @return \jUrlAction or null if the handler does not accept the url
     */
    protected function parseGetParams($infoparsing, \jUrl $url, $matches)
    {
        list($module, $action, $reg, $dynamicValues, $escapes,
             $staticValues, $secondariesActions, $needsHttps) = $infoparsing;
        $params = $url->params;

        $params['module'] = $module;

        if ($secondariesActions && isset($params['action'])) {
            // if the action parameter exists in the current url
            // and if it is one of secondaries actions, then we keep it
            // else we take the action indicated in the url mapping
            if (strpos($params['action'], ':') === false) {
                $params['action'] = 'default:'.$params['action'];
            }
            if (!in_array($params['action'], $secondariesActions) && $action != '') {
                $params['action'] = $action;
            }
        } elseif ($action != '') {
            if (substr($action, -2) == ':*') {
                $action = substr($action, 0, -1);
                // This is an url for a whole controller
                if (isset($matches[1]) && $matches[1]) {
                    $action .= $matches[1];
                } else {
                    $action .= 'index';
                }
                $matches = array();
            }
            // else this is an url for a specific action
            $params['action'] = $action;
        } elseif (count($matches) == 2) {
            // this an url for a whole module
            if ($matches[1] == '/' || $matches[1] == '') {
                $params['action'] = 'default:index';
            } else {
                $pathInfoParts = explode('/', $matches[1]);
                $co = count($pathInfoParts);
                if ($co == 2) {
                    $params['action'] = $pathInfoParts[1].':index';
                } else {
                    $params['action'] = $pathInfoParts[1].':'.$pathInfoParts[2];
                }
            }
            $matches = array();
        }

        // let's merge static parameters
        if ($staticValues) {
            foreach ($staticValues as $n => $v) {
                if (!empty($v) && $v[0] == '$') { // special statique value
                    $typeStatic = $v[1];
                    $v = substr($v, 2);
                    if ($typeStatic == 'l') {
                        App::config()->locale = Locale::langToLocale($v);
                    } elseif ($typeStatic == 'L') {
                        App::config()->locale = $v;
                    }
                }
                $params[$n] = $v;
            }
        }

        // now let's read dynamic parameters
        if (count($matches)) {
            array_shift($matches);
            foreach ($dynamicValues as $k => $name) {
                if (isset($matches[$k])) {
                    if ($escapes[$k] & self::ESCAPE_NON_ASCII) {
                        $params[$name] = \jUrl::unescape($matches[$k]);
                    } else {
                        $params[$name] = $matches[$k];
                        if ($escapes[$k] & self::ESCAPE_LANG) {
                            $v = $matches[$k];
                            if (preg_match('/^\w{2,3}$/', $v, $m)) {
                                App::config()->locale = Locale::langToLocale($v);
                            } else {
                                App::config()->locale = $v;
                                $params[$name] = substr($v, 0, strpos($v, '_'));
                            }
                        } elseif ($escapes[$k] & self::ESCAPE_LOCALE) {
                            $v = $matches[$k];
                            if (preg_match('/^\w{2,3}$/', $v, $m)) {
                                App::config()->locale = $params[$name] = Locale::langToLocale($v);
                            } else {
                                App::config()->locale = $v;
                            }
                        }
                    }
                }
            }
        }
        $urlact = new \jUrlAction($params);
        $urlact->needsHttps = $needsHttps;

        return $urlact;
    }

    protected function parseBasicPathinfo($infoparsing, \jUrl $url)
    {
        $isDefault = $infoparsing['isDefault'];
        $requestType = $infoparsing['requestType'];
        $dedicatedModules = $infoparsing['dedicatedModules'];
        $startModule = $infoparsing['startModule'];
        $startAction = $infoparsing['startAction'];

        // let's try to parse url as /<module>/[<controller>/[<method>/]]
        // only for dedicated modules
        $pathinfo = trim($url->pathInfo, '/');
        $pathInfoParts = explode('/', $pathinfo);
        $params = $url->params;
        $urlact = null;

        if ($pathinfo == '') {
            if (in_array($requestType, $this->entryPointTypeHavingActionInBody)) {
                // in theory, we don't reach this code, since the
                // specific request object doesn't call the url parser..
                // but we need it for some unit tests...
                if (isset($params['module'])) {
                    unset($params['module']);
                }
                if (isset($params['action'])) {
                    unset($params['action']);
                }
                $urlact = new \jUrlAction($params);
            } elseif ($startModule) {
                $params['module'] = $startModule;
                $params['action'] = $startAction;
                $urlact = new \jUrlAction($params);
                if (isset($dedicatedModules[$startModule])) {
                    $urlact->needsHttps = $dedicatedModules[$startModule];
                }
            }
        } elseif (isset($dedicatedModules[$pathInfoParts[0]])) {
            $params['module'] = $pathInfoParts[0];
            $co = count($pathInfoParts);
            if ($co == 1) {
                $params['action'] = 'default:index';
            } elseif ($co == 2) {
                $params['action'] = $pathInfoParts[1].':index';
            } else {
                $params['action'] = $pathInfoParts[1].':'.$pathInfoParts[2];
            }
            $urlact = new \jUrlAction($params);
            $urlact->needsHttps = $dedicatedModules[$pathInfoParts[0]];
        }

        return $urlact;
    }
}
