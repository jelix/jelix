<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Thibault Piront (nuKs)
 *
 * @copyright   2005-2024 Laurent Jouanneau
 * @copyright   2007 Thibault Piront
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

use Jelix\Core\App;

/**
 * Compiler for the url engine. It can parse urls.xml files.
 */
class XmlMapParser
{
    protected $parseInfos;
    protected $createUrlInfos = array();
    protected $createUrlContent = '';
    protected $createUrlContentInc = '';

    protected $createUrlInfosDedicatedModules;

    /**
     * contains, for each entrypoint type, the list of modules that don't appear
     * in any url definitions of any entrypoint of the same type
     * these modules will then be attached to the default entrypoint of the
     * corresponding entrypoint type.
     *
     * @var array key = entrypoint type, value = list of modules
     */
    protected $modulesDedicatedToDefaultEp = array();

    /**
     * contain the UrlMapData object corresponding of the default
     * entrypoint of each type.
     *
     * @var UrlMapData[]
     */
    protected $defaultEntrypointsByType = array();

    /**
     * first element is UrlMapData
     * second element is a parseInfos array.
     */
    protected $entrypoints = array();

    protected $typeparam = array(
        'string' => '([^\/]+)',
        'char' => '([^\/])',
        'letter' => '(\w)',
        'number' => '(\d+)',
        'int' => '(\d+)',
        'integer' => '(\d+)',
        'digit' => '(\d)',
        'date' => '([0-2]\d{3}\-(?:0[1-9]|1[0-2])\-(?:[0-2][1-9]|3[0-1]))',
        'year' => '([0-2]\d{3})',
        'month' => '(0[1-9]|1[0-2])',
        'day' => '([0-2][1-9]|[1-2]0|3[0-1])',
        'path' => '(.*)',
        'locale' => '(\w{2,3}(?:(?:\-|_)\w{2,3})?)',
        'lang' => '(\w{2,3})',
    );

    const ESCAPE_URLENCODE = 0;
    const ESCAPE_SLASH = 1;
    const ESCAPE_NON_ASCII = 2;
    const ESCAPE_LANG = 4;
    const ESCAPE_LOCALE = 8;

    protected $entryPointTypeHavingActionInBody = array('xmlrpc', 'jsonrpc', 'soap');

    /**
     * @var string
     */
    protected $xmlfile = '';

    protected $epHasDefaultUrl = false;

    /**
     * @param SelectorUrlXmlMap $aSelector
     *
     * @return bool true if it is a success
     */
    public function compile($aSelector)
    {
        $sourceFile = $aSelector->getPath();
        $sourceLocalFile = $aSelector->getLocalPath();
        if ($aSelector->localFile == '' || !file_exists($sourceLocalFile)) {
            $sourceLocalFile = '';
        }

        /*
        <urls>
         <classicentrypoint name="index" default="true">
            <url pathinfo="/test/:mois/:annee" module="" action="">
                  <param name="mois" escape="true" regexp="\d{4}"/>
                  <param name="annee" escape="false" />
                  <static name="bla" value="cequejeveux" />
            </url>
            <url handler="" module="" action=""  />
         </classicentrypoint>
        </urls>

        The compiler generates two files.

        It generates a php file for each entrypoint. A file contains a $PARSE_URL
        array:

            $PARSE_URL = array($isDefault, $infoparser, $infoparser, ... )

        where:
            $isDefault: true if it is the default entry point. In this case and
            where the url parser doesn't find a corresponding action, it will
            ignore else it will generate an error

            $infoparser = array('module','action', 'regexp_pathinfo',
                                'handler selector', array('secondaries','actions'),
                                false // needs https or not
                                )
            or
            $infoparser = array('module','action','regexp_pathinfo',
               array('year','month'), // list of dynamic value included in the url,
                                      // alphabetical ascendant order
               array(true, false),    // list of boolean which indicates for each
                                      // dynamic value, if it is an escaped value or not
               array('bla'=>'whatIWant' ), // list of static values
               array('secondaries','actions'),
               false  // need https or not
            )

        It generates an other file common to all entry point. It contains an
        array which contains informations to create urls

            $CREATE_URL = array(
               'news~show@classic' => // the action selector
                  array(0,'entrypoint', https true/false, 'handler selector')
                  or
                  array(1,'entrypoint', https true/false,
                        array('year','month',), // list of dynamic values included in the url
                        array(0, 1..), // list of integer which indicates for each
                                       // dynamic value: 0: urlencode, 1:urlencode except '/', 2:escape, 4: lang, 8: locale
                        "/news/%1/%2/", // the url
                        array('bla'=>'whatIWant' ) // list of static values
                        )
                  or
                  When there are  several urls to the same action, it is an array of this kind of the previous array:
                  array(4, array(1,...), array(1,...)...)

                  or
                  array(2,'entrypoint', https true/false), // for the patterns "@request"
                  or
                  array(3,'entrypoint', https true/false, pathinfobase), // for the patterns "module~@request"
        */

        $this->createUrlInfos = array();
        $this->createUrlContent = "<?php \n";
        $this->createUrlContentInc = '';
        $this->modulesPath = App::getAllModulesPath();
        $this->defaultEntrypointsByType = array();
        $this->modulesDedicatedToDefaultEp = array();

        $this->xmlfile = $aSelector->file;
        $xml = simplexml_load_file($sourceFile);
        if (!$xml) {
            return false;
        }
        $this->parseXml($xml);

        if ($sourceLocalFile) {
            $this->xmlfile = $aSelector->localFile;
            $xml = simplexml_load_file($sourceLocalFile);
            if (!$xml) {
                return false;
            }
            $this->parseXml($xml);
        }

        // search for default entrypoint of each type
        $this->registerDefaultEntrypoints();

        // register all modules dedicated to specific entrypoints
        $this->registerDedicatedModules();

        // write cache files containing parsing informations
        foreach ($this->entrypoints as $epName => $epInfos) {
            list($urlModel, $parseInfos, $createUrlInfosDedicatedModules) = $epInfos;
            $parseContent = "<?php \n";
            $parseContent .= '$GLOBALS[\'SIGNIFICANT_PARSEURL\'][\''.$urlModel->entryPoint.'\'] = '
                            .var_export($parseInfos, true).";\n";
            \jFile::write($aSelector->getCompiledEntrypointFilePath($urlModel->entryPoint), $parseContent);
        }

        // write cache file containing url creation informations
        $this->createUrlContent .= $this->createUrlContentInc;
        $this->createUrlContent .= '$GLOBALS[\'SIGNIFICANT_CREATEURL\'] ='.var_export($this->createUrlInfos, true).";\n";
        \jFile::write($aSelector->getCompiledFilePath(), $this->createUrlContent);

        return true;
    }

    /**
     * @param \SimpleXMLElement $xml
     *
     * @throws MapParserException
     */
    protected function parseXml($xml)
    {
        foreach ($xml->children() as $tagname => $tag) {
            if (!preg_match('/^(.*)entrypoint$/', $tagname, $m)) {
                throw new MapParserException($this->getErrorMsg($tag, "Unknown element {$tagname}"));
            }
            $this->parseEntryPointElement($tag, $m[1]);
        }
    }

    /**
     * extract informations from an <entrypoint> element.
     *
     * @param mixed $type
     *
     * @throws MapParserException
     */
    protected function parseEntryPointElement(\SimpleXMLElement $tag, $type)
    {
        if ($type == '') {
            if (isset($tag['type'])) {
                $type = (string) $tag['type'];
            }
            if ($type == '') {
                $type = 'classic';
            }
        }

        if (!isset($this->modulesDedicatedToDefaultEp[$type])) {
            $this->modulesDedicatedToDefaultEp[$type] = array_combine(
                array_keys($this->modulesPath),
                array_fill(0, count($this->modulesPath), true)
            );
        }

        if (!isset($this->defaultEntrypointsByType[$type])) {
            $this->defaultEntrypointsByType[$type] = null;
        }

        $entryPoint = (string) $tag['name'];
        $isDefault = (isset($tag['default']) && (string) $tag['default'] == 'true');

        if (isset($this->entrypoints[$entryPoint])) {
            // entry point may be already defined, we will add new urls
            list($urlModel, $this->parseInfos,
                $this->createUrlInfosDedicatedModules,
                $this->epHasDefaultUrl) = $this->entrypoints[$entryPoint];

            if (isset($tag['https'])) {
                $urlModel->isHttps = (((string) $tag['https']) == 'true');
            }

            if ($type != $urlModel->requestType) {
                throw new MapParserException($this->getErrorMsg($tag, 'Redefined entry point has a different type '.$type.' than its previous definition'));
            }
        } else {
            $urlModel = new UrlMapData(
                $type,
                (string) $tag['name'],
                (isset($tag['https']) ? (((string) $tag['https']) == 'true') : false)
            );
            $this->parseInfos = array(
                array(
                    'isDefault' => $isDefault,
                    'startModule' => '',
                    'startAction' => '',
                    'requestType' => $urlModel->requestType,
                    'dedicatedModules' => array(),
                ),
            );
            $this->createUrlInfosDedicatedModules = array();
            $this->epHasDefaultUrl = false;

            if ($isDefault) {
                if ($this->defaultEntrypointsByType[$type] !== null) {
                    throw new MapParserException($this->getErrorMsg($tag, 'Only one default entry point for the type '.$type.' is allowed'));
                }
                $this->defaultEntrypointsByType[$type] = $urlModel;
            }
        }

        if (isset($tag['noentrypoint']) && (string) $tag['noentrypoint'] == 'true') {
            $urlModel->entryPointUrl = '';
        }

        $optionalTrailingSlash = (isset($tag['optionalTrailingSlash']) && $tag['optionalTrailingSlash'] == 'true');

        // parse <url> elements
        foreach ($tag->children() as $tagname => $url) {
            $u = clone $urlModel;
            $this->parseUrlElement($url, $u, $optionalTrailingSlash);
        }

        $this->entrypoints[$urlModel->entryPoint] =
            array($urlModel,
                $this->parseInfos,
                $this->createUrlInfosDedicatedModules,
                $this->epHasDefaultUrl,
            );
    }

    /**
     * extract informations from an <url> element.
     *
     * @param bool $optionalTrailingSlash
     */
    protected function parseUrlElement(
        \SimpleXMLElement $url,
        UrlMapData $u,
        $optionalTrailingSlash
    ) {
        $include = isset($url['include']) ? trim((string) $url['include']) : '';
        $handler = isset($url['handler']) ? trim((string) $url['handler']) : '';
        $controller = isset($url['controller']) ? trim((string) $url['controller']) : '';
        $action = isset($url['action']) ? trim((string) $url['action']) : '';
        $u->module = isset($url['module']) ? trim((string) $url['module']) : '';

        if (!$u->module) {
            throw new MapParserException($this->getErrorMsg($url, 'module is missing'));
        }

        if ($handler && $include) {
            throw new MapParserException($this->getErrorMsg($url, 'It cannot have an handler and an include attributes at the same time'));
        }

        if ($u->module && !isset($this->modulesPath[$u->module])) {
            throw new MapParserException($this->getErrorMsg($url, 'the module '.$u->module.' does not exist'));
        }

        $this->modulesDedicatedToDefaultEp[$u->requestType][$u->module] = false;

        if (isset($url['https'])) {
            $u->isHttps = (((string) $url['https']) == 'true');
        }

        if (isset($url['noentrypoint']) && ((string) $url['noentrypoint']) == 'true') {
            $u->entryPointUrl = '';
        }

        if ($include) {
            if ($controller) {
                throw new MapParserException($this->getErrorMsg($url, 'It cannot have a controller and an include attributes at the same time'));
            }
            $this->readInclude($url, $u, $include);

            return;
        }

        $u->isDefault = (isset($url['default']) ? (((string) $url['default']) == 'true') : false);

        if ($u->isDefault) {
            if ($this->epHasDefaultUrl) {
                throw new MapParserException($this->getErrorMsg($url, 'Only one default url by entry point is allowed'));
            }
            $this->epHasDefaultUrl = true;
        }

        // if there is just an <url module="" />, so all actions of this
        // module will be assigned to this entry point.
        if (!$action && !$handler && !$controller) {
            $this->newDedicatedModule($u, $url);

            return;
        }

        if ($controller) {
            if ($action) {
                throw new MapParserException($this->getErrorMsg($url, 'It cannot have a controller and an action attributes at the same time'));
            }
            if ($u->isDefault) {
                throw new MapParserException($this->getErrorMsg($url, 'A controller mapping url cannot be a default url'));
            }
            $u->action = $controller.':*';
            $this->newWholeController($u, $url);

            return;
        }

        $u->setAction($action);

        if (isset($url['actionoverride'])) {
            $u->setActionOverride((string) $url['actionoverride']);
        }

        // if there is an indicated handler, so, for the given module
        // (and optional action), we should call the given handler to
        // parse or create an url
        if ($handler) {
            $this->newHandler($u, $url);

            return;
        }

        // parse dynamic parameters
        list($path, $regexppath) = $this->extractDynamicParams(
            $url,
            $u,
            $optionalTrailingSlash
        );

        // parse static parameters
        $this->extractStaticParams($url, $u);

        if ($path == '' || $path == '/') {
            $u->isDefault = true;
            if ($this->parseInfos[0]['startModule'] != ''
                 && ($this->parseInfos[0]['startModule'] != $u->module
                  || $this->parseInfos[0]['startAction'] != $u->action)
            ) {
                throw new MapParserException($this->getErrorMsg($url, 'There is already a default url for this entrypoint'));
            }
            $this->parseInfos[0]['startModule'] = $u->module;
            $this->parseInfos[0]['startAction'] = $u->action;
        } elseif ($u->isDefault) {
            throw new MapParserException($this->getErrorMsg($url, 'An url not equal to / cannot be default'));
        }

        $this->parseInfos[] = array($u->module, $u->action, '!^'.$regexppath.'$!',
            $u->params, $u->escapes, $u->statics,
            $u->actionOverride, $u->isHttps, );
        $this->appendUrlInfo($u, $path, false);

        if ($u->actionOverride) {
            foreach ($u->actionOverride as $ao) {
                $u->action = $ao;
                $this->appendUrlInfo($u, $path, true);
            }
        }
    }

    /**
     * Verify that there is a default entrypoint for each entrypoint type
     * and register these default entrypoints into url parser/generator data.
     */
    protected function registerDefaultEntrypoints()
    {
        foreach ($this->defaultEntrypointsByType as $type => $urlModel) {
            if (!$urlModel) {
                $entrypoints = array();
                foreach ($this->entrypoints as $epName => $epInfos) {
                    if ($epInfos[0]->requestType == $type) {
                        $entrypoints[] = $epInfos[0];
                    }
                }
                if (count($entrypoints) > 1) {
                    throw new MapParserException('There are several entrypoint of the same type '.$type.', but no one as default');
                }
                if (count($entrypoints) == 1) {
                    $urlModel = $entrypoints[0];
                    $this->entrypoints[$urlModel->entryPoint][1][0]['isDefault'] = true;
                } else {
                    continue;
                }
            }

            // if this is the default entry point for the request type,
            // then we add a rule which will match urls which are not
            // defined.
            $this->createUrlInfos['@'.$urlModel->requestType] = array(2, $urlModel->entryPoint, $urlModel->isHttps);
        }
    }

    /**
     * register all modules dedicated to specific entrypoints
     * into urls parser/generator data.
     */
    protected function registerDedicatedModules()
    {
        foreach ($this->entrypoints as $epName => $epInfos) {
            list($urlModel, $parseInfos, $createUrlInfosDedicatedModules) = $epInfos;

            if ($parseInfos[0]['isDefault']) {
                // add all modules that isn't used in other entrypoint, to this
                // entry point
                foreach ($this->modulesDedicatedToDefaultEp[$urlModel->requestType] as $mod => $ok) {
                    if ($ok) {
                        $parseInfos[0]['dedicatedModules'][$mod] = $urlModel->isHttps;
                        $this->createUrlInfosDedicatedModules[$mod.'~*@'.$urlModel->requestType]
                            = array(3, $urlModel->entryPointUrl, $urlModel->isHttps, true, '');
                    }
                }
                $this->modulesDedicatedToDefaultEp[$urlModel->requestType] = array();
            }

            // check if there is a default url
            if ($parseInfos[0]['startModule'] == ''
                && !in_array($urlModel->requestType, $this->entryPointTypeHavingActionInBody)) {
                if (count($parseInfos[0]['dedicatedModules']) == 1) {
                    foreach ($parseInfos[0]['dedicatedModules'] as $module => $isHttps) {
                        $parseInfos[0]['startModule'] = $module;
                        $parseInfos[0]['startAction'] = 'default:index';
                    }
                    $arr = array(1, $urlModel->entryPointUrl, $urlModel->isHttps,
                        array(), array(), '/', false, array(), );
                    $this->createUrlInfos[$parseInfos[0]['startModule'].'~'.$parseInfos[0]['startAction'].'@'.$parseInfos[0]['requestType']] = $arr;
                }
                /*else if ($parseInfos[0]['isDefault']) {
                    throw new MapParserException ('Default url is missing for the entry point '.$epName);
                }*/
                else {
                    // for inexistant default url, let's say that / is a 404 error
                    $parseInfos[0]['startModule'] = 'jelix';
                    $parseInfos[0]['startAction'] = 'error:notfound';
                }
            }

            $this->entrypoints[$epName][1] = $parseInfos;

            $c = count($createUrlInfosDedicatedModules);
            foreach ($createUrlInfosDedicatedModules as $actionSelector => $inf) {
                if ($c > 1) {
                    $inf[3] = false;
                }
                $this->createUrlInfos[$actionSelector] = $inf;
            }
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param string            $message
     *
     * @return string
     */
    protected function getErrorMsg($xml, $message)
    {
        $xml = $xml->asXML();
        $xml = substr($xml, 0, strpos($xml, '>') + 1);

        return $this->xmlfile.': '.$message.' ('.$xml.')';
    }

    /**
     * returns the combination between $rootPathInfo and the pathinfo
     * attribute of the given $url.
     *
     * @param mixed $rootPathInfo
     *
     * @return string full pathinfo, or "" if both are empty or "/"
     */
    protected function getFinalPathInfo(\SimpleXMLElement $url, $rootPathInfo)
    {
        $subpathinfo = '';
        $pathinfo = '';

        if (isset($url['pathinfo'])) {
            $subpathinfo = ltrim((string) $url['pathinfo'], '/');
        }

        if ($subpathinfo == '') {
            if ($rootPathInfo != '/') {
                $pathinfo = $rootPathInfo;
            }
        } elseif ($rootPathInfo != '/') {
            $pathinfo = $rootPathInfo.'/'.$subpathinfo;
        } else {
            $pathinfo = $rootPathInfo.$subpathinfo;
        }

        return $pathinfo;
    }

    protected function checkStaticPathInfo(\SimpleXMLElement $url)
    {
        if (!isset($url['pathinfo'])) {
            return true;
        }
        $pathInfo = (string) $url['pathinfo'];
        if (preg_match('/(?<!\\\\)\\\\:([a-zA-Z_0-9]+)/', $pathInfo)) {
            throw new MapParserException($this->getErrorMsg($url, 'pathinfo can content dynamic part only for actions'));
        }

        return true;
    }

    /**
     * all actions of this module will be assigned to this entry point.
     */
    protected function newDedicatedModule(UrlMapData $u, \SimpleXMLElement $url)
    {
        $pathinfo = (isset($url['pathinfo']) ? ((string) $url['pathinfo']) : '');

        if ($u->isDefault && $pathinfo != '' && $pathinfo != '/') {
            throw new MapParserException($this->getErrorMsg($url, 'Url with a pathinfo different from "/" cannot be the default url when the corresponding module is assigned on a dedicated entrypoint'));
        }
        $this->checkStaticPathInfo($url);

        if ($u->isDefault) {
            if ($this->parseInfos[0]['startModule'] != '') {
                throw new MapParserException($this->getErrorMsg($url, 'There is already a default url for this entrypoint'));
            }
            $this->parseInfos[0]['startModule'] = $u->module;
            $this->parseInfos[0]['startAction'] = 'default:index';
            // add parser and creator information for the default action
            $this->parseInfos[] = array($u->module, 'default:index', '!^/?$!',
                array(), array(), array(),
                array(), $u->isHttps, );
            $u->action = 'default:index';
            $this->appendUrlInfo($u, '/', false);
            $u->action = '';
        } elseif ($pathinfo != '/' && $pathinfo != '') {
            $pathinfo = '/'.trim($pathinfo, '/');
            $this->parseInfos[] = array($u->module, '',
                '!^'.preg_quote($pathinfo, '!').'(/.*)?$!',
                array(), array(), array(), false, $u->isHttps, );
            $this->createUrlInfosDedicatedModules[$u->getFullSel()] =
                array(3, $u->entryPointUrl, $u->isHttps, true, $pathinfo);

            return;
        }
        $this->parseInfos[0]['dedicatedModules'][$u->module] = $u->isHttps;
        $this->createUrlInfosDedicatedModules[$u->getFullSel()] =
            array(3, $u->entryPointUrl, $u->isHttps, true, '');
    }

    /**
     * all methods of a specific controller will be assigned to this entry point.
     *
     * @param mixed $rootPathInfo
     */
    protected function newWholeController(UrlMapData $u, \SimpleXMLElement $url, $rootPathInfo = '')
    {
        $this->checkStaticPathInfo($url);
        $pathinfo = $this->getFinalPathInfo($url, $rootPathInfo);

        $this->parseInfos[] = array($u->module, $u->action,
            '!^'.preg_quote($pathinfo, '!').'(?:/(.*))?$!',
            array(), array(), array(), false, $u->isHttps, );
        $this->createUrlInfos[$u->getFullSel()] = array(5, $u->entryPointUrl, $u->isHttps, $pathinfo);
    }

    /**
     * list all modules path.
     */
    protected $modulesPath = array();

    /**
     * @param mixed $rootPathInfo
     */
    protected function newHandler(
        UrlMapData $u,
        \SimpleXmlElement $url,
        $rootPathInfo = '/'
    ) {
        $class = (string) $url['handler'];
        // we must have a module name in the selector, because, during the parsing of
        // the url in the request process, we are not still in a module context
        $p = strpos($class, '~');
        if ($p === false) {
            $selclass = $u->module.'~'.$class;
        } elseif ($p == 0) {
            $selclass = $u->module.$class;
        } else {
            $selclass = $class;
        }

        $s = new SelectorUrlHandler($selclass);
        if (!isset($url['action'])) {
            $u->action = '*';
        }
        $this->checkStaticPathInfo($url);
        $pathinfo = $this->getFinalPathInfo($url, $rootPathInfo);

        if ($pathinfo != '/') {
            $regexp = '!^'.preg_quote($pathinfo, '!').'(/.*)?$!';
        } else {
            if ($u->isDefault) {
                if ($this->parseInfos[0]['startModule'] != '') {
                    throw new MapParserException($this->getErrorMsg($url, 'There is already a default url for this entrypoint'));
                }
                if ($u->action == '*') {
                    throw new MapParserException($this->getErrorMsg($url, '"default" attribute is not allowed on url handler without specific action'));
                }
                $this->parseInfos[0]['startModule'] = $u->module;
                $this->parseInfos[0]['startAction'] = $u->action;
            }
            $regexp = '';
        }

        $this->createUrlContentInc .= "include_once('".$s->getPath()."');\n";
        $this->parseInfos[] = array($u->module, $u->action, $regexp, $selclass,
            $u->actionOverride, $u->isHttps, );
        $this->createUrlInfos[$u->getFullSel()] = array(0, $u->entryPointUrl, $u->isHttps,
            $selclass, $pathinfo, );
        if ($u->actionOverride) {
            foreach ($u->actionOverride as $ao) {
                $u->action = $ao;
                $this->createUrlInfos[$u->getFullSel()] =
                    array(0, $u->entryPointUrl, $u->isHttps, $selclass, $pathinfo);
            }
        }
    }

    /**
     * extract all dynamic parts of a pathinfo, read <param> elements.
     *
     * @param \SimpleXmlElement $url                   the url element
     * @param bool              $optionalTrailingSlash
     * @param string            $rootPathInfo          the path info prefix
     *
     * @return array first element is the final pathinfo
     *               second element is the correponding regular expression
     */
    protected function extractDynamicParams(
        \SimpleXmlElement $url,
        UrlMapData $u,
        $optionalTrailingSlash,
        $rootPathInfo = '/'
    ) {
        if (isset($url['optionalTrailingSlash'])) {
            $optionalTrailingSlash = ((string) $url['optionalTrailingSlash'] == 'true');
        }

        $pathInfo = $this->getFinalPathInfo($url, $rootPathInfo);
        if ($pathInfo != '/' && $pathInfo != '') {
            $regexppath = $this->buildDynamicParamsRegexp($url, $pathInfo, $u);
            if ($optionalTrailingSlash) {
                if (substr($regexppath, -1) == '/') {
                    $regexppath .= '?';
                } else {
                    $regexppath .= '\/?';
                }
            }
        } else {
            $regexppath = '\/?';
        }

        return array($pathInfo, $regexppath);
    }

    /**
     * build the regexp corresponding to dynamic parts of a pathinfo.
     *
     * @param \SimpleXmlElement $url      the url element
     * @param string            $path     the path info
     * @param mixed             $pathinfo
     *
     * @return string the corresponding regular expression
     */
    protected function buildDynamicParamsRegexp(
        \SimpleXmlElement $url,
        $pathinfo,
        UrlMapData $u
    ) {
        $regexppath = preg_quote($pathinfo, '!');
        if (preg_match_all('/(?<!\\\\)\\\\:([a-zA-Z_0-9]+)/', $regexppath, $m, PREG_PATTERN_ORDER)) {
            $u->params = $m[1];

            // process parameters which are declared in a <param> element
            foreach ($url->param as $var) {
                $name = (string) $var['name'];
                $k = array_search($name, $u->params);
                if ($k === false) {
                    // TODO error
                    continue;
                }

                $type = '';
                if (isset($var['type'])) {
                    $type = (string) $var['type'];
                    if (isset($this->typeparam[$type])) {
                        $regexp = $this->typeparam[$type];
                    } else {
                        $regexp = $this->typeparam['string'];
                    }
                } elseif (isset($var['regexp'])) {
                    $regexp = '('.(string) $var['regexp'].')';
                } else {
                    $regexp = $this->typeparam['string'];
                }

                $u->escapes[$k] = UrlActionMapper::ESCAPE_URLENCODE;
                if ($type == 'path') {
                    $u->escapes[$k] = UrlActionMapper::ESCAPE_SLASH;
                } elseif (isset($var['escape'])) {
                    $u->escapes[$k] = (((string) $var['escape']) == 'true' ?
                        UrlActionMapper::ESCAPE_NON_ASCII : UrlActionMapper::ESCAPE_URLENCODE);
                }

                if ($type == 'lang') {
                    $u->escapes[$k] |= UrlActionMapper::ESCAPE_LANG;
                } elseif ($type == 'locale') {
                    $u->escapes[$k] |= UrlActionMapper::ESCAPE_LOCALE;
                }

                $regexppath = str_replace('\:'.$name, $regexp, $regexppath);
            }

            // process parameters that are only declared in the pathinfo
            foreach ($u->params as $k => $name) {
                if (isset($u->escapes[$k])) {
                    continue;
                }
                $u->escapes[$k] = UrlActionMapper::ESCAPE_URLENCODE;
                $regexppath = str_replace('\:'.$name, '([^\/]+)', $regexppath);
            }
        }

        return str_replace('\\\\\\:', '\\:', $regexppath);
    }

    /**
     * @param \SimpleXmlElement $url  the url element
     * @param string            $path the path info
     */
    protected function extractStaticParams(
        \SimpleXmlElement $url,
        UrlMapData $u
    ) {
        foreach ($url->static as $var) {
            $t = '';
            if (isset($var['type'])) {
                switch ((string) $var['type']) {
                    case 'lang': $t = '$l';

                        break;

                    case 'locale': $t = '$L';

                        break;

                    default:
                        throw new MapParserException($this->getErrorMsg($var, 'invalid type on a <static> element'));
                }
            }
            $u->statics[(string) $var['name']] = $t.(string) $var['value'];
        }
    }

    /**
     * register the given url informations.
     *
     * @param string $path
     * @param mixed  $secondaryAction
     */
    protected function appendUrlInfo(UrlMapData $u, $path, $secondaryAction)
    {
        $cuisel = $u->getFullSel();
        $arr = array(1, $u->entryPointUrl, $u->isHttps, $u->params, $u->escapes,
            $path, $secondaryAction, $u->statics, );
        if (isset($this->createUrlInfos[$cuisel])) {
            if ($this->createUrlInfos[$cuisel][0] == 4) {
                $this->createUrlInfos[$cuisel][] = $arr;
            } else {
                $this->createUrlInfos[$cuisel] = array(4, $this->createUrlInfos[$cuisel], $arr);
            }
        } else {
            $this->createUrlInfos[$cuisel] = $arr;
        }
    }

    /**
     * @param mixed $file
     */
    protected function readInclude(
        \SimpleXmlElement $url,
        UrlMapData $uInfo,
        $file
    ) {
        if (isset($url['default'])) {
            throw new MapParserException($this->getErrorMsg($url, '"default" attribute is not allowed with include'));
        }

        if (isset($url['action'])) {
            throw new MapParserException($this->getErrorMsg($url, 'action is forbidden with include'));
        }

        if (isset($url['pathinfo'])) {
            $this->checkStaticPathInfo($url);
            $pathinfo = '/'.trim((string) $url['pathinfo'], '/');
        } else {
            $pathinfo = '/';
        }

        $path = $this->modulesPath[$uInfo->module];

        if (!file_exists($path.$file)) {
            throw new MapParserException($this->getErrorMsg($url, 'include file '.$file.' of the module '.$uInfo->module.' does not exist'));
        }

        $xml = simplexml_load_file($path.$file);
        if (!$xml) {
            throw new MapParserException($this->getErrorMsg($url, 'include file '.$file.' of the module '.$uInfo->module.' is not a valid xml file'));
        }
        $optionalTrailingSlash = (isset($xml['optionalTrailingSlash']) && $xml['optionalTrailingSlash'] == 'true');

        $mainXmlFile = $this->xmlfile;
        $this->xmlfile = $uInfo->module.'/'.$file;

        if (!isset($xml->url)) {
            $this->newDedicatedModule($uInfo, $url);
            return;
        }

        foreach ($xml->url as $url) {
            $u = clone $uInfo;

            if (isset($url['module'])) {
                throw new MapParserException($this->getErrorMsg($url, 'module is forbidden in module url files'));
            }

            if (isset($url['include'])) {
                throw new MapParserException($this->getErrorMsg($url, 'include is forbidden in module url files'));
            }

            if (isset($url['default'])) {
                throw new MapParserException($this->getErrorMsg($url, '"default" attribute is forbidden in module url files'));
            }

            if (isset($url['controller'])) {
                if (isset($url['action'])) {
                    throw new MapParserException($this->getErrorMsg($url, 'It cannot have a controller and an action attributes at the same time'));
                }
                $this->newWholeController($u, $url, $pathinfo);

                continue;
            }

            $u->setAction((string) $url['action']);

            if (isset($url['actionoverride'])) {
                $u->setActionOverride((string) $url['actionoverride']);
            }

            // if there is an indicated handler, so, for the given module
            // (and optional action), we should call the given handler to
            // parse or create an url
            if (isset($url['handler'])) {
                $this->newHandler($u, $url, $pathinfo);

                continue;
            }

            // parse dynamic parameters
            list($path, $regexppath) = $this->extractDynamicParams(
                $url,
                $u,
                $optionalTrailingSlash,
                $pathinfo
            );

            if ($path == '' || $path == '/') {
                $u->isDefault = true;
                if ($this->parseInfos[0]['startModule'] != '') {
                    throw new MapParserException($this->getErrorMsg($url, 'There is already a default url for this entrypoint'));
                }
                $this->parseInfos[0]['startModule'] = $u->module;
                $this->parseInfos[0]['startAction'] = $u->action;
            }

            // parse static parameters
            $this->extractStaticParams($url, $u);

            $this->parseInfos[] = array($u->module, $u->action, '!^'.$regexppath.'$!',
                $u->params, $u->escapes, $u->statics,
                $u->actionOverride, $u->isHttps, );
            $this->appendUrlInfo($u, $path, false);
            if ($u->actionOverride) {
                foreach ($u->actionOverride as $ao) {
                    $u->action = $ao;
                    $this->appendUrlInfo($u, $path, true);
                }
            }
        }
        $this->xmlfile = $mainXmlFile;
    }
}
