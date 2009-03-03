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
    public function parseFromRequest($request, $params){
        global $gJConfig;

        if ($gJConfig->urlengine['enableParser']){

            $sel = new jSelectorUrlCfgSig($gJConfig->urlengine['significantFile']);
            jIncluder::inc($sel);
            $snp = $gJConfig->urlengine['urlScriptIdenc'];
            $file=JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$sel->file.'.'.$snp.'.entrypoint.php';
            if(file_exists($file)){
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

        if ($gJConfig->urlengine['enableParser']){

            $sel = new jSelectorUrlCfgSig($gJConfig->urlengine['significantFile']);
            jIncluder::inc($sel);
            $basepath = $gJConfig->urlengine['basePath'];
            if(strpos($scriptNamePath, $basepath) === 0){
                $snp = substr($scriptNamePath,strlen($basepath));
            }else{
                $snp = $scriptNamePath;
            }
            $pos = strrpos($snp,$gJConfig->urlengine['entrypointExtension']);
            if($pos !== false){
                $snp = substr($snp,0,$pos);
            }
            $snp = rawurlencode($snp);
            $file=JELIX_APP_TEMP_PATH.'compiled/urlsig/'.$sel->file.'.'.$snp.'.entrypoint.php';
            if(file_exists($file)){
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

        /*if(substr($pathinfo,-1) == '/' && $pathinfo != '/'){
                $pathinfo = substr($pathinfo,0,-1);
        }*/

        $urlact = null;
        $isDefault = false;
        $url = new jUrl($scriptNamePath, $params, $pathinfo);

        foreach($this->dataParseUrl as $k=>$infoparsing){
            // le premier paramètre indique si le point d'entré actuelle est un point d'entré par défaut ou non
            if($k==0){
                $isDefault=$infoparsing;
                continue;
            }

            if(count($infoparsing) < 5){
                // on a un tableau du style
                // array( 0=> 'module', 1=>'action', 2=>'selecteur handler', 3=>array('actions','secondaires'))
                $s = new jSelectorUrlHandler($infoparsing[2]);
                $c =$s->className.'UrlsHandler';
                $handler =new $c();

                $url->params['module']=$infoparsing[0];

                // si une action est présente dans l'url actuelle
                // et qu'elle fait partie des actions secondaires, alors on la laisse
                // sinon on prend celle indiquée dans la conf
                if ($infoparsing[3] && isset($params['action'])) {
                    if(strpos($params['action'], ':') === false) {
                        $params['action'] = 'default:'.$params['action'];
                    }
                    if(in_array($params['action'], $infoparsing[3]))
                        $url->params['action']=$params['action']; // action peut avoir été écrasé par une itération précédente
                    else
                        $url->params['action']=$infoparsing[1];
                }else{
                    $url->params['action']=$infoparsing[1];
                }
                // appel au handler
                if($urlact = $handler->parse($url)){
                    break;
                }
            }else{
                /* on a un tableau du style
                array( 0=>'module', 1=>'action', 2=>'regexp_pathinfo',
                3=>array('annee','mois'), // tableau des valeurs dynamiques, classées par ordre croissant
                4=>array(true, false), // tableau des valeurs escapes
                5=>array('bla'=>'cequejeveux' ) // tableau des valeurs statiques
                6=>false ou array('act','act'...) // autres actions secondaires autorisées
                */
                if(preg_match ($infoparsing[2], $pathinfo, $matches)){
                    if($infoparsing[0] !='')
                        $params['module']=$infoparsing[0];

                    // si une action est présente dans l'url actuelle
                    // et qu'elle fait partie des actions secondaires, alors on la laisse
                    // sinon on prend celle indiquée dans la conf

                    if($infoparsing[6] && isset($params['action']) ) {
                        if(strpos($params['action'], ':') === false) {
                            $params['action'] = 'default:'.$params['action'];
                        }
                        if(!in_array($params['action'], $infoparsing[6]) && $infoparsing[1] !='') {
                            $params['action']=$infoparsing[1];
                        }

                    } else {
                        if($infoparsing[1] !='')
                            $params['action']=$infoparsing[1];
                    }

                    // on fusionne les parametres statiques
                    if ($infoparsing[5]) {
                        $params = array_merge ($params, $infoparsing[5]);
                    }

                    if(count($matches)){
                        array_shift($matches);
                        foreach($infoparsing[3] as $k=>$name){
                            if(isset($matches[$k])){
                                if($infoparsing[4][$k]){
                                    $params[$name] = jUrl::unescape($matches[$k]);
                                }else{
                                    $params[$name] = $matches[$k];
                                }
                            }
                        }
                    }
                    $urlact = new jUrlAction($params);
                    break;
                }
            }
        }
        if(!$urlact) {
            if($isDefault && $pathinfo == ''){
               // si on n'a pas trouvé de correspondance, mais que c'est l'entry point
               // par defaut pour le type de request courant, alors on laisse passer..
               $urlact = new jUrlAction($params);
            } else {
               try{
                   $urlact = jUrl::get($gJConfig->urlengine['notfoundAct'],array(),jUrl::JURLACTION);
               }catch(Exception $e){
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
    public function create( $urlact){

        if($this->dataCreateUrl == null){
            $sel = new jSelectorUrlCfgSig($GLOBALS['gJConfig']->urlengine['significantFile']);
            jIncluder::inc($sel);
            $this->dataCreateUrl = & $GLOBALS['SIGNIFICANT_CREATEURL'];
        }

        /*
        a) recupere module~action@request -> obtient les infos pour la creation de l'url
        b) récupère un à un les parametres indiqués dans params à partir de jUrl
        c) remplace la valeur récupérée dans le result et supprime le paramètre de l'url
        d) remplace scriptname de jUrl par le resultat
        */

        $url = new jUrl('',$urlact->params,'');

        $module = $url->getParam('module', jContext::get());
        $action = $url->getParam('action');

        $id = $module.'~'.$action.'@'.$urlact->requestType;
        $urlinfo = null;
        if (isset ($this->dataCreateUrl [$id])){
            $urlinfo = $this->dataCreateUrl[$id];
            $url->delParam('module');
            $url->delParam('action');
        }else{
            $id = $module.'~*@'.$urlact->requestType;
            if (isset ($this->dataCreateUrl [$id])){
                $urlinfo = $this->dataCreateUrl[$id];
                $url->delParam('module');
            }else{
                $id = '@'.$urlact->requestType;
                if (isset ($this->dataCreateUrl [$id])){
                    $urlinfo = $this->dataCreateUrl[$id];
                }else{
                    throw new Exception("Significant url engine doesn't find corresponding url to this action :".$module.'~'.$action.'@'.$urlact->requestType);
                }
            }
        }
        /*
        urlinfo =
            array(0,'entrypoint', https true/false,'selecteur handler')
            ou
            array(1,'entrypoint', https true/false, 
                    array('annee','mois','jour','id','titre'), // liste des paramètres de l'url à prendre en compte
                    array(true, false..), // valeur des escapes
                    "/news/%1/%2/%3/%4-%5", // forme de l'url
                    false, //indique si  c'est une action surchargeante
                    )
            ou
            array(2,'entrypoint', https true/false,); pour les clés du type "@request"
            array(3,'entrypoint', https true/false); pour les clés du type "module~@request"
            array(4, array(1,..), array(1,..)...);
        */
        if($urlinfo[0]==4){
            $l = count($urlinfo);
            $urlinfofound = null;
            for($i=1; $i < $l; $i++){
                $ok = true;
                foreach($urlinfo[$i][7] as $n=>$v){
                    if($url->getParam($n,'') != $v){
                        $ok = false;
                        break;
                    }
                }
                if($ok){
                    $urlinfofound = $urlinfo[$i];
                    break;
                }
            }
            if($urlinfofound !== null){
                $urlinfo = $urlinfofound;
            }else{
                $urlinfo = $urlinfo[1];
            }
        }

        $url->scriptName = $GLOBALS['gJConfig']->urlengine['basePath'].$urlinfo[1];
        if($urlinfo[2])
            $url->scriptName = 'https://'.$_SERVER['HTTP_HOST'].$url->scriptName;

        if($urlinfo[1] && !$GLOBALS['gJConfig']->urlengine['multiview']){
            $url->scriptName.=$GLOBALS['gJConfig']->urlengine['entrypointExtension'];
        }
        // pour certains types de requete, les paramètres ne sont pas dans l'url
        // donc on les supprime
        // c'est un peu crade de faire ça en dur ici, mais ce serait lourdingue
        // de charger la classe request pour savoir si on peut supprimer ou pas
        if(in_array($urlact->requestType ,array('xmlrpc','jsonrpc','soap'))){
            $url->clearParam();
            return $url;
        }

        if($urlinfo[0]==0){
            $s = new jSelectorUrlHandler($urlinfo[3]);
            $c =$s->resource.'UrlsHandler';
            $handler =new $c();
            $handler->create($urlact, $url);
        }elseif($urlinfo[0]==1){
            $pi = $urlinfo[5];
            foreach ($urlinfo[3] as $k=>$param){
                if($urlinfo[4][$k]){
                    $pi=str_replace(':'.$param, jUrl::escape($url->getParam($param,''),true), $pi);
                }else{
                    $pi=str_replace(':'.$param, $url->getParam($param,''), $pi);
                }
                $url->delParam($param);
            }
            $url->pathInfo = $pi;
            if($urlinfo[6])
                $url->setParam('action',$action);
            // removed parameters corresponding to static values
            foreach($urlinfo[7] as $name=>$value){
                $url->delParam($name);
            }
        }elseif($urlinfo[0]==3){
            $url->delParam('module');
        }

        return $url;
    }
}
