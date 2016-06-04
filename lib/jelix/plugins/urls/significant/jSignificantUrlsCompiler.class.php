<?php
/**
* @package     jelix
* @subpackage  urls_engine
* @author      Laurent Jouanneau
* @contributor Thibault Piront (nuKs)
* @copyright   2005-2012 Laurent Jouanneau
* @copyright   2007 Thibault Piront
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class significantUrlInfoParsing {
    public $entryPoint = '';
    public $entryPointUrl = '';
    public $isHttps = false;
    public $isDefault = false;
    public $action = '';
    public $module = '';
    public $actionOverride = false;
    public $requestType = '';
    public $statics = array();
    public $params = array();
    public $escapes = array();

    function __construct($rt, $ep, $isDefault, $isHttps) {
        $this->requestType = $rt;
        $this->entryPoint = $this->entryPointUrl = $ep;
        $this->isDefault = $isDefault;
        $this->isHttps = $isHttps;
    }

    function getFullSel() {
        if ($this->action) {
            $act = $this->action;
            if (substr($act,-1) == ':') // this is a rest action
                // we should add index because jSelectorAct resolve a "ctrl:" as "ctrl:index"
                // and then create the corresponding selector so url create infos will be found
                $act .= 'index';
        }
        else {
            $act = '*';
        }
        return $this->module.'~'.$act.'@'.$this->requestType;
    }

    function setAction($action) {
        if (strpos($action, ':') === false) {
            $this->action = 'default:'.$action;
        }
        else {
            $this->action = $action;
        }
    }

    function setActionOverride($actionoverride) {
        $this->actionOverride = preg_split("/[\s,]+/", $actionoverride);
        foreach ($this->actionOverride as &$each) {
            if (strpos($each, ':') === false) {
                $each = 'default:'.$each;
            }
        }
    }
}

/**
* Compiler for significant url engine
* @package  jelix
* @subpackage urls_engine
*/
class jSignificantUrlsCompiler implements jISimpleCompiler{

    protected $parseInfos;
    protected $createUrlInfos;
    protected $createUrlContent;
    protected $createUrlContentInc;

    /**
     * first element is significantUrlInfoParsing
     * second element is a parseInfos array
     */
    protected $entrypoints = array();

    protected $typeparam = array(
        'string'=>'([^\/]+)',
        'char'=>'([^\/])',
        'letter'=>'(\w)',
        'number'=>'(\d+)',
        'int'=>'(\d+)',
        'integer'=>'(\d+)',
        'digit'=>'(\d)',
        'date'=>'([0-2]\d{3}\-(?:0[1-9]|1[0-2])\-(?:[0-2][1-9]|3[0-1]))',
        'year'=>'([0-2]\d{3})',
        'month'=>'(0[1-9]|1[0-2])',
        'day'=>'([0-2][1-9]|[1-2]0|3[0-1])',
        'path'=>'(.*)',
        'locale'=>'(\w{2,3}(?:(?:\-|_)\w{2,3})?)',
        'lang'=>'(\w{2,3})'
    );

    const ESCAPE_URLENCODE = 0;
    const ESCAPE_SLASH = 1;
    const ESCAPE_NON_ASCII = 2;
    const ESCAPE_LANG = 4;
    const ESCAPE_LOCALE = 8;

    /**
     *
     */
    public function compile($aSelector) {

        $sourceFile = $aSelector->getPath();
        $cachefile = $aSelector->getCompiledFilePath();

        $xml = simplexml_load_file ($sourceFile);
        if (!$xml) {
           return false;
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
                  array(3,'entrypoint', https true/false), // for the patterns "module~@request"
        */

        $this->createUrlInfos = array();
        $this->createUrlContent = "<?php \nif (jApp::config()->compilation['checkCacheFiletime'] &&( \n";
        $this->createUrlContent .= "filemtime('".$sourceFile.'\') > '.filemtime($sourceFile);
        $this->createUrlContentInc = '';
        $this->modulesPath = jApp::getAllModulesPath();

        // contains, for each entrypoint type, the list of modules that don't appear
        // in any url definitions of any entrypoint of the same type
        // theses modules will then be attached to the default entrypoint of the
        // corresponding entrypoint type.
        // key = entrypoint type, value = list of modules
        $modulesDedicatedToDefaultEp = array();

        foreach ($xml->children() as $tagname => $tag) {
            if (!preg_match("/^(.*)entrypoint$/", $tagname, $m)) {
                throw new Exception("Urls xml file: Unknown element $tagname");
            }
            $type = $m[1];
            if ($type == '') {
                if (isset($tag['type']))
                    $type = (string)$tag['type'];
                if ($type == '')
                    $type = 'classic';
            }

            if (!isset($modulesDedicatedToDefaultEp[$type])) {
                $modulesDedicatedToDefaultEp[$type] = array_combine(array_keys($this->modulesPath),
                                                        array_fill(0, count($this->modulesPath), true));
            }

            $defaultUrl = new significantUrlInfoParsing (
                $type,
                (string)$tag['name'],
                (isset($tag['default']) ? (((string)$tag['default']) == 'true'):false),
                (isset($tag['https']) ? (((string)$tag['https']) == 'true'):false)
            );

            if (isset($tag['noentrypoint']) && (string)$tag['noentrypoint'] == 'true') {
                $defaultUrl->entryPointUrl = '';
            }

            $optionalTrailingSlash = (isset($tag['optionalTrailingSlash']) && $tag['optionalTrailingSlash'] == 'true');

            $this->parseInfos = array(
                    array(
                        "isDefault" => $defaultUrl->isDefault,
                        "requestType" => $defaultUrl->requestType,
                        "dedicatedModules" => array()
                    )
                );

            // if this is the default entry point for the request type,
            // then we add a rule which will match urls which are not
            // defined.
            if ($defaultUrl->isDefault) {
                $this->createUrlInfos['@'.$defaultUrl->requestType] = array(2, $defaultUrl->entryPoint, $defaultUrl->isHttps);
            }

            $createUrlInfosDedicatedModules = array();

            foreach ($tag->children() as $tagname => $url) {
                $u = clone $defaultUrl;
                if (!isset($url['module']) || trim((string)$url['module']) == '') {
                    throw new Exception('urls definition: module is missing on a <url> element');
                }

                $u->module = trim((string)$url['module']);

                if (!isset($this->modulesPath[$u->module])) {
                    throw new Exception ('urls.xml: the module '.$u->module.' does not exist');
                }

                $modulesDedicatedToDefaultEp[$u->requestType][$u->module] = false;

                if (isset($url['https'])) {
                    $u->isHttps = (((string)$url['https']) == 'true');
                }

                if (isset($url['noentrypoint']) && ((string)$url['noentrypoint']) == 'true') {
                    $u->entryPointUrl = '';
                }

                $u->isDefault = (isset($url['default']) ? (((string)$url['default']) == 'true'):false);

                if (isset($url['include'])) {
                    $this->readInclude($url, $u);
                    continue;
                }

                // in the case of a non default entry point, if there is just an
                // <url module="" />, so all actions of this module will be assigned
                // to this entry point.
                if (!isset($url['action']) && !isset($url['handler'])) {
                    $this->parseInfos[0]["dedicatedModules"][$u->module] = array($u->isHttps, $u->isDefault);
                    $createUrlInfosDedicatedModules[$u->getFullSel()] = array(3, $u->entryPointUrl, $u->isHttps, true);
                    continue;
                }

                $u->setAction((string)$url['action']);

                if (isset($url['actionoverride'])) {
                    $u->setActionOverride((string)$url['actionoverride']);
                }

                // if there is an indicated handler, so, for the given module
                // (and optional action), we should call the given handler to
                // parse or create an url
                if (isset($url['handler'])) {
                    $this->newHandler($u, $url);
                    continue;
                }

                // parse dynamic parameters
                list($path, $regexppath) = $this->extractDynamicParams($url, $u, $optionalTrailingSlash);

                // parse static parameters
                $this->extractStaticParams($url, $u);

                $this->parseInfos[] = array($u->module, $u->action, '!^'.$regexppath.'$!',
                                            $u->params, $u->escapes, $u->statics,
                                            $u->actionOverride, $u->isHttps);
                $this->appendUrlInfo($u, $path, false);

                if ($u->actionOverride) {
                    foreach ($u->actionOverride as $ao) {
                        $u->action = $ao;
                        $this->appendUrlInfo($u, $path, true);
                    }
                }
            } // end of loop on urls

            $this->entrypoints[$defaultUrl->entryPoint] = array($defaultUrl,
                                                                $this->parseInfos,
                                                                $createUrlInfosDedicatedModules);
        } // end of loop on entrypoints

        // write cache files containing parsing informations
        foreach ($this->entrypoints as $epName => $epInfos) {
            list ($defaultUrl, $parseInfos, $createUrlInfosDedicatedModules) = $epInfos;

            if ($defaultUrl->isDefault) {
                foreach($modulesDedicatedToDefaultEp[$defaultUrl->requestType] as $mod => $ok) {
                    if ($ok) {
                        $parseInfos[0]['dedicatedModules'][$mod] = array($defaultUrl->isHttps, false);
                        $createUrlInfosDedicatedModules[$mod.'~*@'.$defaultUrl->requestType]
                            = array(3, $defaultUrl->entryPointUrl, $defaultUrl->isHttps, true);
                    }
                }
                $modulesDedicatedToDefaultEp[$defaultUrl->requestType] = array();
            }

            $parseContent = "<?php \n";
            $parseContent .= '$GLOBALS[\'SIGNIFICANT_PARSEURL\'][\''.rawurlencode($defaultUrl->entryPoint).'\'] = '
                            .var_export($parseInfos, true).";\n?>";

            jFile::write(jApp::tempPath('compiled/urlsig/'.$aSelector->file.'.'.rawurlencode($defaultUrl->entryPoint).'.entrypoint.php'),$parseContent);

            $c = count($createUrlInfosDedicatedModules);
            foreach ($createUrlInfosDedicatedModules as $actionSelector=>$inf) {
                if ($c > 1) {
                    $inf[3] = false;
                }
                $this->createUrlInfos[$actionSelector] = $inf;
            }
        }

        // write cache file containing url creation informations
        $this->createUrlContent .= ")) { return false; } else {\n";
        $this->createUrlContent .= $this->createUrlContentInc;
        $this->createUrlContent .= '$GLOBALS[\'SIGNIFICANT_CREATEURL\'] ='.var_export($this->createUrlInfos, true).";\nreturn true;";
        $this->createUrlContent .= "\n}\n";
        jFile::write(jApp::tempPath('compiled/urlsig/'.$aSelector->file.'.creationinfos_15.php'), $this->createUrlContent);
        return true;
    }

    /**
     * list all modules path
     */
    protected $modulesPath = array();

    /**
     * @param significantUrlInfoParsing $u
     * @param simpleXmlElement $url
    */
    protected function newHandler($u, $url, $pathinfo = '') {
        $class = (string)$url['handler'];
        // we must have a module name in the selector, because, during the parsing of
        // the url in the request process, we are not still in a module context
        $p = strpos($class,'~');
        if ($p === false)
            $selclass = $u->module.'~'.$class;
        elseif ($p == 0)
            $selclass = $u->module.$class;
        else
            $selclass = $class;

        $s = new jSelectorUrlHandler($selclass);
        if (!isset($url['action'])) {
            $u->action = '*';
        }
        $regexp = '';

        if (isset($url['pathinfo'])) {
            $pathinfo .= '/'.trim((string)$url['pathinfo'], '/');
        }

        if ($pathinfo != '/') {
            $regexp = '!^'.preg_quote($pathinfo, '!').'(/.*)?$!';
        }

        $this->createUrlContentInc .= "include_once('".$s->getPath()."');\n";
        $this->parseInfos[] = array($u->module, $u->action, $regexp, $selclass,
                                    $u->actionOverride, $u->isHttps);
        $this->createUrlInfos[$u->getFullSel()] = array(0, $u->entryPointUrl, $u->isHttps, $selclass, $pathinfo);
        if ($u->actionOverride) {
            foreach ($u->actionOverride as $ao) {
                $u->action = $ao;
                $this->createUrlInfos[$u->getFullSel()] = array(0, $u->entryPointUrl, $u->isHttps, $selclass, $pathinfo);
            }
        }
    }

    /**
     * extract all dynamic parts of a pathinfo, read <param> elements
     * @param simpleXmlElement $url the url element
     * @param significantUrlInfoParsing $u
     * @param boolean $optionalTrailingSlash
     * @param string $rootPathInfo  the path info prefix
     * @return array  first element is the final pathinfo
     *                second element is the correponding regular expression
     */
    protected function extractDynamicParams($url, significantUrlInfoParsing $u,
                                            $optionalTrailingSlash, $rootPathInfo='') {
        if (isset($url['pathinfo'])) {
            if ($rootPathInfo) {
                $path = $rootPathInfo.($rootPathInfo !='/'?'/':'').trim((string)$url['pathinfo'],'/');
            }
            else {
                $path = (string)$url['pathinfo'];
            }
            $regexppath = $this->buildDynamicParamsRegexp($url, $path, $u);
        }
        else {
            $regexppath = '.*';
            $path = '';
        }

        if (isset($url['optionalTrailingSlash'])) {
            $optionalTrailingSlash = ($url['optionalTrailingSlash'] == 'true');
        }
        if ($optionalTrailingSlash) {
            if (substr($regexppath, -1) == '/') {
                $regexppath .= '?';
            }
            else {
                $regexppath .= '\/?';
            }
        }
        return array($path, $regexppath);
    }

    /**
     * build the regexp corresponding to dynamic parts of a pathinfo
     * @param simpleXmlElement $url the url element
     * @param string $path  the path info
     * @param significantUrlInfoParsing $u
     * @return string the correponding regular expression
     */
    protected function buildDynamicParamsRegexp($url, $pathinfo, significantUrlInfoParsing $u) {
        $regexppath = preg_quote($pathinfo , '!');
        if (preg_match_all("/(?<!\\\\)\\\:([a-zA-Z_0-9]+)/", $regexppath, $m, PREG_PATTERN_ORDER)) {
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
                    $type = (string)$var['type'];
                    if (isset($this->typeparam[$type])) {
                        $regexp = $this->typeparam[$type];
                    }
                    else {
                        $regexp = $this->typeparam['string'];
                    }
                }
                elseif (isset ($var['regexp'])) {
                    $regexp = '('.(string)$var['regexp'].')';
                }
                else {
                    $regexp = $this->typeparam['string'];
                }

                $u->escapes[$k] = 0;
                if ($type == 'path') {
                    $u->escapes[$k] = 1;
                }
                else if (isset($var['escape'])) {
                    $u->escapes[$k] = (((string)$var['escape']) == 'true'?2:0);
                }

                if ($type == 'lang') {
                    $u->escapes[$k] |= 4;
                }
                else if ($type == 'locale') {
                    $u->escapes[$k] |= 8;
                }

                $regexppath = str_replace('\:'.$name, $regexp, $regexppath);
            }

            // process parameters that are only declared in the pathinfo
            foreach ($u->params as $k=>$name) {
                if (isset($u->escapes[$k])) {
                    continue;
                }
                $u->escapes[$k] = 0;
                $regexppath = str_replace('\:'.$name, '([^\/]+)', $regexppath);
            }
        }
        $regexppath = str_replace("\\\\\\:", "\:", $regexppath);
        return $regexppath;
    }

    /**
     *
     * @param simpleXmlElement $url the url element
     * @param string $path  the path info
     * @param significantUrlInfoParsing $u
     */
    protected function extractStaticParams($url, significantUrlInfoParsing $u) {
        foreach ($url->static as $var) {
            $t = "";
            if (isset($var['type'])) {
                switch ((string) $var['type']) {
                    case 'lang': $t = '$l'; break;
                    case 'locale': $t = '$L'; break;
                    default:
                        throw new Exception('urls definition: invalid type on a <static> element');
                }
            }
            $u->statics[(string)$var['name']] = $t . (string)$var['value'];
        }
    }

    /**
     * register the given url informations
     * @param significantUrlInfoParsing $u
     * @param string $path
     */
    protected function appendUrlInfo($u, $path, $secondaryAction) {
        $cuisel = $u->getFullSel();
        $arr = array(1, $u->entryPointUrl, $u->isHttps, $u->params, $u->escapes, $path, $secondaryAction, $u->statics);
        if (isset($this->createUrlInfos[$cuisel])) {
            if ($this->createUrlInfos[$cuisel][0] == 4) {
                $this->createUrlInfos[$cuisel][] = $arr;
            }
            else {
                $this->createUrlInfos[$cuisel] = array(4, $this->createUrlInfos[$cuisel], $arr);
            }
        }
        else {
            $this->createUrlInfos[$cuisel] = $arr;
        }
    }

    /**
     * @param simpleXmlElement $url
     * @param significantUrlInfoParsing $uInfo
    */
    protected function readInclude($url, $uInfo) {

        $file = (string)$url['include'];
        $pathinfo = '/'.trim((string)$url['pathinfo'], '/');

        $path = $this->modulesPath[$uInfo->module];

        if (!file_exists($path.$file))
            throw new Exception ('urls.xml: include file '.$file.' of the module '.$uInfo->module.' does not exist');

        $this->createUrlContent .= " || filemtime('".$path.$file.'\') > '.filemtime($path.$file)."\n";

        $xml = simplexml_load_file ($path.$file);
        if (!$xml) {
           throw new Exception ('urls.xml: include file '.$file.' of the module '.$uInfo->module.' is not a valid xml file');
        }
        $optionalTrailingSlash = (isset($xml['optionalTrailingSlash']) && $xml['optionalTrailingSlash'] == 'true');

        foreach ($xml->url as $url) {
            $u = clone $uInfo;

            $u->setAction((string)$url['action']);

            if (isset($url['actionoverride'])) {
                $u->setActionOverride((string)$url['actionoverride']);
            }

            // if there is an indicated handler, so, for the given module
            // (and optional action), we should call the given handler to
            // parse or create an url
            if (isset($url['handler'])) {
                $this->newHandler($u, $url, $pathinfo);
                continue;
            }

            // parse dynamic parameters
            list($path, $regexppath) = $this->extractDynamicParams($url, $u, $optionalTrailingSlash, $pathinfo);

            // parse static parameters
            $this->extractStaticParams($url, $u);

            $this->parseInfos[] = array($u->module, $u->action, '!^'.$regexppath.'$!',
                                        $u->params, $u->escapes, $u->statics,
                                        $u->actionOverride, $u->isHttps);
            $this->appendUrlInfo($u, $path, false);
            if ($u->actionOverride) {
                foreach ($u->actionOverride as $ao) {
                    $u->action = $ao;
                    $this->appendUrlInfo($u, $path, true);
                }
            }
        }
    }
}
