<?php
/**
 * @package     jelix
 * @subpackage  urls_engine
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2005-2009 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * a specific selector for the xml files which contains the configuration of the engine
 * @package  jelix
 * @subpackage urls_engine
 * @author      Laurent Jouanneau
 * @copyright   2005-2006 Laurent Jouanneau
 */
class jSelectorUrlCfgSig extends jSelectorCfg {
    public $type = 'urlcfgsig';

    public function getCompiler(){
        require_once(dirname(__FILE__).'/jSignificantUrlsCompiler.class.php');
        $o = new jSignificantUrlsCompiler();
        return $o;
    }
    public function getCompiledFilePath (){ return JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$this->file.'.creationinfos.php';}
}

/**
 * a specific selector for user url handler
 * @package  jelix
 * @subpackage urls_engine
 * @author      Laurent Jouanneau
 * @copyright   2005-2006 Laurent Jouanneau
 */
class jSelectorUrlHandler extends jSelectorClass {
    public $type = 'urlhandler';
    protected $_suffix = '.urlhandler.php';
}

/**
 * interface for user url handler
 * @package  jelix
 * @subpackage urls_engine
 * @author      Laurent Jouanneau
 * @copyright   2005-2006 Laurent Jouanneau
 */
interface jIUrlSignificantHandler {
    /**
    * create the jUrlAction corresponding to the given jUrl. Return false if it doesn't correspond
    * @param jUrl
    * @return jUrlAction|false
    */
    public function parse($url);

    /**
    * fill the given jurl object depending the jUrlAction object
    * @param jUrlAction $urlact
    * @param jUrl $url
    */
    public function create($urlact, $url);
}

/**
 * an url engine to parse,analyse and create significant url
 * it needs an urls.xml file in the config directory (see documentation)
 * @package  jelix
 * @subpackage urls_engine
 * @author      Laurent Jouanneau
 * @copyright   2005-2008 Laurent Jouanneau
 */
class significantUrlEngine implements jIUrlEngine {

    /**
    * data to create significant url
    * @var array
    */
    protected $dataCreateUrl = null;

    /**
    * data to parse and anaylise significant url, and to determine action, module etc..
    * @var array
    */
    protected $dataParseUrl =  null;

    /**
     * Parse a url from the request
     * @param jRequest $request
     * @param array  $params            url parameters
     * @return jUrlAction
     * @since 1.1
     */
    public function parseFromRequest ($request, $params) {
        global $gJConfig;

        if ($gJConfig->urlengine['enableParser']) {

            $sel = new jSelectorUrlCfgSig($gJConfig->urlengine['significantFile']);
            jIncluder::inc($sel);
            $snp  = $gJConfig->urlengine['urlScriptIdenc'];
            $file = JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$sel->file.'.'.$snp.'.entrypoint.php';
            if (file_exists($file)) {
                require($file);
                $this->dataCreateUrl = & $GLOBALS['SIGNIFICANT_CREATEURL']; // fourni via le jIncluder ligne 99
                $this->dataParseUrl = & $GLOBALS['SIGNIFICANT_PARSEURL'][$snp];
                return $this->_parse($request->urlScript, $request->urlPathInfo, $params);
            }
        }

        $urlact = new jUrlAction($params);
        return $urlact;
    }

    /**
    * Parse some url components
    * @param string $scriptNamePath    /path/index.php
    * @param string $pathinfo          the path info part of the url (part between script name and query)
    * @param array  $params            url parameters (query part e.g. $_REQUEST)
    * @return jUrlAction
    */
    public function parse($scriptNamePath, $pathinfo, $params){
        global $gJConfig;

        if ($gJConfig->urlengine['enableParser']) {

            $sel = new jSelectorUrlCfgSig($gJConfig->urlengine['significantFile']);
            jIncluder::inc($sel);
            $basepath = $gJConfig->urlengine['basePath'];
            if (strpos($scriptNamePath, $basepath) === 0) {
                $snp = substr($scriptNamePath,strlen($basepath));
            }
            else {
                $snp = $scriptNamePath;
            }
            $pos = strrpos($snp, $gJConfig->urlengine['entrypointExtension']);
            if ($pos !== false) {
                $snp = substr($snp,0,$pos);
            }
            $snp = rawurlencode($snp);
            $file = JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$sel->file.'.'.$snp.'.entrypoint.php';
            if (file_exists($file)) {
                require($file);
                $this->dataCreateUrl = & $GLOBALS['SIGNIFICANT_CREATEURL']; // fourni via le jIncluder ligne 127
                $this->dataParseUrl = & $GLOBALS['SIGNIFICANT_PARSEURL'][$snp];
                return $this->_parse($scriptNamePath, $pathinfo, $params);
            }
        }
        $urlact = new jUrlAction($params);
        return $urlact;
    }

    /**
    *
    * @param string $scriptNamePath    /path/index.php
    * @param string $pathinfo          the path info part of the url (part between script name and query)
    * @param array  $params            url parameters (query part e.g. $_REQUEST)
    * @return jUrlAction
    */
    protected function _parse($scriptNamePath, $pathinfo, $params){
        global $gJConfig;

        $urlact = null;
        $isDefault = false;
        $url = new jUrl($scriptNamePath, $params, $pathinfo);

        foreach ($this->dataParseUrl as $k=>$infoparsing) {
            // the first element indicates if the entry point is a default entry point or not
            if ($k==0) {
                $isDefault = $infoparsing;
                continue;
            }

            if (count($infoparsing) < 6) {
                list($module, $action, $reg, $selectorHandler, $secondariesActions) = $infoparsing;
                $url2 = clone $url;
                if ($reg != '') {
                    if (preg_match($reg, $pathinfo, $m))
                        $url2->pathInfo = isset($m[1])?$m[1]:'/';
                    else
                        continue;
                }
                $s = new jSelectorUrlHandler($selectorHandler);
                $c = $s->className.'UrlsHandler';
                $handler = new $c();

                $url2->params['module'] = $module;

                // if the action parameter exists in the current url
                // and if it is one of secondaries actions, then we keep it
                // else we take the action indicated in the url mapping
                if ($secondariesActions && isset($params['action'])) {
                    if (strpos($params['action'], ':') === false) {
                        $params['action'] = 'default:'.$params['action'];
                    }
                    if (in_array($params['action'], $secondariesActions))
                        // action peut avoir été écrasé par une itération précédente
                        $url2->params['action'] = $params['action'];
                    else
                        $url2->params['action'] = $action;
                }
                else {
                    $url2->params['action'] = $action;
                }
                // appel au handler
                if ($urlact = $handler->parse($url2)) {
                    break;
                }
            }
            elseif (preg_match ($infoparsing[2], $pathinfo, $matches)) {

                /* we have this array
                array( 0=>'module', 1=>'action', 2=>'regexp_pathinfo',
                3=>array('year','month'), // list of dynamic value included in the url,
                                      // alphabetical ascendant order
                4=>array(true, false),    // list of boolean which indicates for each
                                      // dynamic value, if it is an escaped value or not
                5=>array('bla'=>'whatIWant' ), // list of static values
                6=>false or array('secondaries','actions')
                */
                list($module, $action, $reg, $dynamicValues, $escapes, $staticValues, $secondariesActions) = $infoparsing;
                if (isset($params['module']) && $params['module'] !== $module)
                    continue;

                if ($module != '')
                    $params['module'] = $module;

                // if the action parameter exists in the current url
                // and if it is one of secondaries actions, then we keep it
                // else we take the action indicated in the url mapping
                if ($secondariesActions && isset($params['action']) ) {
                    if (strpos($params['action'], ':') === false) {
                        $params['action'] = 'default:'.$params['action'];
                    }
                    if (!in_array($params['action'], $secondariesActions) && $action !='') {
                        $params['action'] = $action;
                    }
                }
                else {
                    if ($action !='')
                        $params['action'] = $action;
                }

                // let's merge static parameters
                if ($staticValues) {
                    $params = array_merge ($params, $staticValues);
                }

                // now let's read dynamic parameters
                if (count($matches)) {
                    array_shift($matches);
                    foreach ($dynamicValues as $k=>$name){
                        if (isset($matches[$k])) {
                            if ($escapes[$k]) {
                                $params[$name] = jUrl::unescape($matches[$k]);
                            }
                            else {
                                $params[$name] = $matches[$k];
                            }
                        }
                    }
                }
                $urlact = new jUrlAction($params);
                break;
            }
        }
        if (!$urlact) {
            if ($isDefault && $pathinfo == '') {
                // if we didn't find the url in the mapping, and if this is the default
                // entry point, then we do anything
                $urlact = new jUrlAction($params);
            }
            else {
                try {
                    $urlact = jUrl::get($gJConfig->urlengine['notfoundAct'], array(), jUrl::JURLACTION);
                }
                catch (Exception $e) {
                    $urlact = new jUrlAction(array('module'=>'jelix', 'action'=>'error:notfound'));
                }
            }
        }
        return $urlact;
    }

    /**
    * Create a jurl object with the given action data
    * @param jUrlAction $url  information about the action
    * @return jUrl the url correspondant to the action
    * @author      Laurent Jouanneau
    * @copyright   2005 CopixTeam, 2005-2006 Laurent Jouanneau
    *   very few lines of code are copyrighted by CopixTeam, written by Laurent Jouanneau
    *   and released under GNU Lesser General Public Licence,
    *   in an experimental version of Copix Framework v2.3dev20050901,
    *   http://www.copix.org.
    */
    public function create($urlact) {

        if ($this->dataCreateUrl == null) {
            $sel = new jSelectorUrlCfgSig($GLOBALS['gJConfig']->urlengine['significantFile']);
            jIncluder::inc($sel);
            $this->dataCreateUrl = & $GLOBALS['SIGNIFICANT_CREATEURL'];
        }

        $url = new jUrl('', $urlact->params, '');

        $module = $url->getParam('module', jContext::get());
        $action = $url->getParam('action');

        // let's try to retrieve informations corresponding
        // to the given action. this informations will allow us to build
        // the url
        $id = $module.'~'.$action.'@'.$urlact->requestType;
        $urlinfo = null;
        if (isset ($this->dataCreateUrl [$id])) {
            $urlinfo = $this->dataCreateUrl[$id];
            $url->delParam('module');
            $url->delParam('action');
        }
        else {
            $id = $module.'~*@'.$urlact->requestType;
            if (isset ($this->dataCreateUrl[$id])) {
                $urlinfo = $this->dataCreateUrl[$id];
                if ($urlinfo[0] != 3 || $urlinfo[3] === true)
                    $url->delParam('module');
            }
            else {
                $id = '@'.$urlact->requestType;
                if (isset ($this->dataCreateUrl [$id])) {
                    $urlinfo = $this->dataCreateUrl[$id];
                }
                else {
                    throw new Exception("Significant url engine doesn't find corresponding url to this action :".$module.'~'.$action.'@'.$urlact->requestType);
                }
            }
        }
        /*
        urlinfo =
          or array(0,'entrypoint', https true/false, 'handler selector', 'basepathinfo')
          or array(1,'entrypoint', https true/false,
                  array('year','month',), // list of dynamic values included in the url
                  array(true, false..), // list of boolean which indicates for each
                                        // dynamic value, if it is an escaped value or not
                  "/news/%1/%2/", // the url
                  true/false, // false : this is a secondary action
                  array('bla'=>'whatIWant' ) // list of static values
                  )
          or array(2,'entrypoint', https true/false), // for the patterns "@request"
          or array(3,'entrypoint', https true/false), // for the patterns "module~@request"
          or array(4, array(1,...), array(1,...)...)
        */
        if ($urlinfo[0] == 4) {
            // an action is mapped to several urls
            // so it isn't finished. Let's find building information
            // into the array
            $l = count($urlinfo);
            $urlinfofound = null;
            for ($i=1; $i < $l; $i++) {
                $ok = true;
                // verify that given static parameters of the action correspond
                // to those defined for this url
                foreach ($urlinfo[$i][7] as $n=>$v) {
                    if ($url->getParam($n,'') != $v) {
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
            }
            else {
                $urlinfo = $urlinfo[1];
            }
        }

        // at this step, we have informations to build the url

        $url->scriptName = $GLOBALS['gJConfig']->urlengine['basePath'].$urlinfo[1];
        if ($urlinfo[2])
            $url->scriptName = 'https://'.$_SERVER['HTTP_HOST'].$url->scriptName;

        if ($urlinfo[1] && !$GLOBALS['gJConfig']->urlengine['multiview']) {
            $url->scriptName .= $GLOBALS['gJConfig']->urlengine['entrypointExtension'];
        }

        // pour certains types de requete, les paramètres ne sont pas dans l'url
        // donc on les supprime
        // c'est un peu crade de faire ça en dur ici, mais ce serait lourdingue
        // de charger la classe request pour savoir si on peut supprimer ou pas
        if (in_array($urlact->requestType, array('xmlrpc','jsonrpc','soap'))) {
            $url->clearParam();
            return $url;
        }

        if ($urlinfo[0] == 0) {
            $s = new jSelectorUrlHandler($urlinfo[3]);
            $c = $s->resource.'UrlsHandler';
            $handler = new $c();
            $handler->create($urlact, $url);
            if ($urlinfo[4] != '') {
                $url->pathInfo = $urlinfo[4].$url->pathInfo;
            }
        }
        elseif($urlinfo[0] == 1) {
            $pi = $urlinfo[5];
            foreach ($urlinfo[3] as $k=>$param){
                if ($urlinfo[4][$k]) {
                    $pi = str_replace(':'.$param, jUrl::escape($url->getParam($param,''),true), $pi);
                }
                else {
                    $pi = str_replace(':'.$param, $url->getParam($param,''), $pi);
                }
                $url->delParam($param);
            }
            $url->pathInfo = $pi;
            if ($urlinfo[6])
                $url->setParam('action',$action);
            // removed parameters corresponding to static values
            foreach ($urlinfo[7] as $name=>$value) {
                $url->delParam($name);
            }
        }
        elseif ($urlinfo[0] == 3) {
            if ($urlinfo[3]) {
                $url->delParam('module');
            }
        }

        return $url;
    }
}
