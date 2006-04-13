<?php
/**
* @package    jelix
* @subpackage utils
* @version    $Id:$
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixZone)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

class jZone {
    /**
    * If we're using cache in this zone
    * @var boolean
    */
    protected $_useCache = false;

    /**
    * nom des parametres de la zone permettant de l'identifiant de façon unique
    * @var array
    */
    protected $_cacheParams = array ();

    /**
    * Paramètres d'exécution passés à la zone.
    */
    protected $_params;


    protected $_tplname='';
    protected $_tpl=null;
    protected $_cancelCache=false;

    function __construct($params=array()){
        $this->_params = $params;
    }

    /**
    *
    * @static
    */
    public static function processZone ($name, $params=array ()){
        return self::_callZone($name, 'getContent', $params);
    }

    /**
    *
    * @static
    */
    public static function clearZone ($name, $params=array ()){
        return self::_callZone($name, 'clearCache', $params);
    }

    public static function clearAllZone($name=''){
        $dir = JELIX_APP_TEMP_PATH.'zonecache/';
        if(!file_exists($dir)) return;

        if($name !=''){
            $sel = new jSelectorZone($name);
            $fic = '~'.$sel->module.'~zone'.strtolower($sel->resource).'~';
        }else{
            $fic = '~';
        }

        if ($dh = opendir($dir)) {
           while (($file = readdir($dh)) !== false) {
               if(strpos($file, $fic) === 0){
                   unlink($dir.$file);
               }
           }
           closedir($dh);
       }
    }

   /**
    * Creation d'un objet zone et appel de sa méthode processZone.
    * @param string $name le nom de la zone à instancier.
    * @param array   $params un tableau a passer a la fonction processZone de l'objet zone.
    */
    protected static function  _callZone($name,$method, &$params){

        $sel = new jSelectorZone($name);
        jContext::push ($sel->module);

        $fileName = $sel->getPath();
        require_once($fileName);

        $objName = 'Zone'.$sel->resource;
        $zone = new $objName ($params);
        $toReturn = $zone->$method ();

        jContext::pop ();
        return $toReturn;
    }


    /**
    * Méthode qui gère la zone
    * Selon si le cache doit être utilisé, et est valide ou non, on retournera le contenu du cache
    * ou on calculera la zone puis la retournera après l'avoir stockée de nouveau dans le cache
    * @param array  $Params les paramètres de contexte pour la zone. (généralement le contenu de l'url)
    * @return   string  le contenu de la zone
    * @access public
    */
    public function getContent (){
        if ($this->_useCache){
            $f = $this->_getCacheFile();
            if(file_exists($f)){
                $content=file_get_contents($f);
            }else{
                $this->_cancelCache=false;
                $content=$this->_createContent();
                if(!$this->_cancelCache){
                    $file = new jFile($f);
                    $file->write($content);
                }
            }
        }else{
            $content=$this->_createContent();
        }
        return $content;
    }

    /**
    * Méthode qui efface le cache de la zone
    */
    public function clearCache (){
        if ($this->_useCache){
            $f = $this->_getCacheFile();
            if(file_exists($f)){
                unlink($f);
            }
        }
    }


    /**
    */
    function _createContent (){
        if($this->_tplname != ''){
            $this->_tpl = new jTpl();
            $this->_tpl->assign($this->_params);
            $this->_prepareTpl();
            return $this->_tpl->fetch($this->_tplname);
        }
        return '';
    }

    protected function _prepareTpl(){
    }

    /**
    *
    * @access private
    */
    private function _getCacheFile (){
        $module = jContext::get ();
        $ar = $this->_params;
        ksort($ar);
        $id=md5(serialize($ar));
        return JELIX_APP_TEMP_PATH.'zonecache/~'.$module.'~'.strtolower(get_class($this)).'~'.$id.'.php';
    }

    /**
    * gets the value of a parameter, if defined. Returns the default value instead.
    * @param string $paramName the parameter name
    * @param mixed $defaultValue the parameter default value
    * @return mixed the param value
    */
    function getParam ($paramName, $defaultValue=null){
       return array_key_exists ($paramName, $this->_params) ? $this->_params[$paramName] : $defaultValue;
    }



}
?>