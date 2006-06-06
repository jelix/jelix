<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* inspiré des selecteurs de Copix http://www.copix.org
*/

/**
* Instancie un objet jISelector en fonction du selecteur donné
* @param string $id  le selecteur au format : "type:module|fichier"
* @return jISelector    l'objet selecteur correspondant
*/
class jSelectorFactory {
    function create ($id){
        if(preg_match("/^([a-z]{3,5})\:([\w~\/\.]+)$/", $id, $m)){
            $cname='jSelector'.$m[1];
            if(class_exists($cname)){
                $sel = new $cname($m[2]);
                if($sel->isValid())
                    return $sel;
            }
        }
        trigger_error (jLocale::get ('jelix~errors.selector.unknown', $id));
        $ret = null;
        return $ret;
    }
}

/**
 * interface d'un objet selecteur
 */
interface jISelector {
    public function getPath ();
    public function isValid();
    public function getCompiledFilePath ();
    public function getCompiler();
    public function useMultiSourceCompiler();
    public function toString($full=false);
}

/**
* classe de base pour les selecteurs des fichiers de modules
*/
abstract class jSelectorModule implements jISelector {
    public $type = '_module';
    public $module = null;
    public $resource = null;

    private $_valid;
    protected $_dirname='';
    protected $_suffix='';
    protected $_cacheSuffix='.php';
    protected $_path;
    protected $_cachePath;
    protected $_compiler = null;
    protected $_compilerPath;
    protected $_useMultiSourceCompiler=false;

    function __construct($sel){
        if(preg_match("/^(([\w\.]+)~)?([\w\.]+)$/", $sel, $m)){
            $this->_valid = true;
            if($m[1]!='' && $m[2]!=''){
                $this->module = $m[2];
            }else{
                $this->module = jContext::get ();
            }
            $this->resource = $m[3];
            $this->_createPath();
        }else{
            $this->_valid = false;
        }
    }

    public function getPath (){
        $this->_createPath();
        return $this->_path;
    }

    public function isValid(){
        return $this->_valid;
    }

    public function getCompiledFilePath (){
        $this->_createCachePath();
        return $this->_cachePath;
    }

    public function getCompiler(){
        if($this->_compiler == null) return null;
        $n = $this->_compiler;
        require_once($this->_compilerPath);
        $o = new $n();
        return $o;
    }

    public function useMultiSourceCompiler(){
        return $this->_useMultiSourceCompiler;
    }

    public function toString($full=false){
        if($full)
            return $this->type.':'.$this->module.'~'.$this->resource;
        else
            return $this->module.'~'.$this->resource;
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            $this->_valid = false;
            return;
        }
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.$this->_suffix;
        if (!is_readable ($this->_path)){
            $this->_path=='';
            $this->_valid = false;
        }
    }

    protected function _createCachePath(){
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/'.$this->_dirname.$this->module.'~'.$this->resource.$this->_cacheSuffix;
    }
}


/**
 * sélecteur d'action
 */
class jSelectorAct extends jSelectorModule {
    public $type = 'act';
    public $request = '';
    public $controller = '';
    public $method='';
    protected $_dirname='actions/';

    function __construct($sel){
        global $gJCoord;

        if(preg_match("/^(?:([\w\.]+|\#)~)?([\w\.]+|\#)(?:@([\w\.]+))?$/", $sel, $m)){
            $this->_valid = true;
            if($m[1]!=''){
                if($m[1] == '#')
                    $this->module = $gJCoord->moduleName;
                else
                    $this->module = $m[1];
            }else{
                $this->module = jContext::get ();
            }
            if($m[2] == '#')
                $this->resource = $gJCoord->actionName;
            else
                $this->resource = $m[2];

            $r = explode('_',$this->resource);

            if(count($r) == 1){
                $this->controller = 'default';
                $this->method = $r[0]==''?'index':$r[0];
            }else{
                $this->controller = $r[0]=='' ? 'default':$r[0];
                $this->method = $r[1]==''?'index':$r[1];
            }

            if(isset($m[3]) && $m[3] != '')
                $this->request = $m[3];
            else
                $this->request = $gJCoord->request->type;

            $this->_createPath();
        }else{
            $this->_valid = false;
        }
        parent::__construct($sel);
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            $this->_path=='';
            $this->_valid = false;
        }else{
            $this->_path = $gJConfig->_modulesPathList[$this->module].'controllers/'.$this->controller.'.'.$this->request.'.php';
        }
    }

    protected function _createCachePath(){
        $this->_cachePath = '';
    }

    public function toString($full=false){
        if($full)
            return $this->type.':'.$this->module.'~'.$this->resource.'@'.$this->request;
        else
            return $this->module.'~'.$this->resource.'@'.$this->request;
    }

    public function getClass(){
        return 'CT'.$this->controller;
    }

}

/**
 * sélecteur de classes métiers
 */
class jSelectorClass extends jSelectorModule {
    public $type = 'class';
    protected $_dirname = 'classes/';
    protected $_suffix = '.class.php';

    protected function _createCachePath(){
        $this->_cachePath = '';
    }
}

/**
 * sélecteur de fichier de localisation
 */
class jSelectorLoc extends jSelectorModule {
    public $type = 'loc';

    function __construct($sel, $locale=null, $charset=null){
        global $gJConfig;
        if ($locale === null){
            $locale = $gJConfig->defaultLocale;
        }
        if ($charset === null){
            $charset = $gJConfig->defaultCharset;
        }

        $this->_dirname =  'locales/' .$locale.'/';
        $this->_suffix = '.'.$charset.'.properties';
        $this->_cacheSuffix = '.'.$charset.'.php';
        $this->_compiler='jLocalesCompiler';
        $this->_compilerPath=JELIX_LIB_CORE_PATH.'jLocalesCompiler.class.php';

        parent::__construct($sel);
    }
}

/**
 * sélecteur de fichier dao xml
 */
class jSelectorDao extends jSelectorModule {
    public $type = 'dao';
    public $driver;
    protected $_dirname = 'daos/';
    protected $_suffix = '.dao.xml';
    protected $_where;

    function __construct($sel, $driver, $isprofil=true){
        if($isprofil){
            $p = jDb::getProfil($driver);
            $this->driver= $p['driver'];
        }else{
            $this->driver=$driver;
        }
        $this->_compiler='jDaoCompiler';
        $this->_compilerPath=JELIX_LIB_DAO_PATH.'jDaoCompiler.class.php';
        parent::__construct($sel);
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            $this->_valid = false;
            return;
        }

        // on regarde si le dao a été redéfini
        $overloadedPath = JELIX_APP_VAR_PATH.'overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix;
        if (is_readable ($overloadedPath)){
           $this->_path = $overloadedPath;
           $this->_where = 1;
           return;
        }
        // et sinon, on regarde si le dao existe dans le module en question
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.$this->_suffix;

        if (!is_readable ($this->_path)){
            $this->_path=='';
            $this->_valid = false;
        }
        $this->_where = 0;
    }

    protected function _createCachePath(){
        $d = array('modules/','overloaded/');
        // on ne partage pas le même cache pour tous les emplacements possibles
        // au cas où un overload était supprimé
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/daos/'.$d[$this->_where].$this->module.'~'.$this->resource.'~'.$this->driver.$this->_cacheSuffix;
    }

    public function getDaoClass(){
        return 'cDao_'.$this->module.'_'.$this->resource.'_'.$this->driver;
    }
    public function getDaoRecordClass(){
        return 'cDaoRecord_'.$this->module.'_'.$this->resource.'_'.$this->driver;
    }
}

/**
 * sélecteur de fichier de template
 */
class jSelectorTpl extends jSelectorModule {
    public $type = 'tpl';
    protected $_dirname = 'templates/';
    protected $_suffix = '.tpl';
    protected $_where;

    function __construct($sel){
        $this->_compiler='jTplCompiler';
        $this->_compilerPath=JELIX_LIB_TPL_PATH.'jTplCompiler.class.php';
        parent::__construct($sel);
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            $this->_valid = false;
            return;
        }

        // on regarde si il y a un template redéfinie pour le theme courant
         $this->_path = JELIX_APP_VAR_PATH.'themes/'.$gJConfig->defaultTheme.'/'.$this->module.'/'.$this->resource.'.tpl';
         if (is_readable ($this->_path)){
            $this->_where = 1;
            return;
         }

        // on regarde si il y a un template redéfinie dans le theme par defaut
        $this->_path = JELIX_APP_VAR_PATH.'themes/default/'.$this->module.'/'.$this->resource.'.tpl';
        if (is_readable ($this->_path)){
           $this->_where = 2;
           return;
        }

        // et sinon, on regarde si le template existe dans le module en question
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.'.tpl';

        if (!is_readable ($this->_path)){
            $this->_path=='';
            $this->_valid = false;
        }
        $this->_where = 0;
    }

    protected function _createCachePath(){
       $d = array('modules/','themes/'.$GLOBALS['gJConfig']->defaultTheme.'/','themes/default/');
       // on ne partage pas le même cache pour tous les emplacements possibles
       // au cas où un overload était supprimé
       $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/templates/'.$d[$this->_where].$this->module.'~'.$this->resource.$this->_cacheSuffix;
    }
}

/**
 * sélecteur de zone
 */
class jSelectorZone extends jSelectorModule {
    public $type = 'zone';
    protected $_dirname = 'zones/';
    protected $_suffix = '.zone.php';

    protected function _createCachePath(){
        $this->_cachePath = '';
    }
}

/**
 * sélecteur de fichier de formulaire
 */
class jSelectorForm extends jSelectorModule {
    public $type = 'form';

    function __construct($sel){
        global $gJConfig;

        $this->_dirname =  'forms/';
        $this->_suffix = '.form.xml';
        $this->_cacheSuffix = '.php';
        $this->_compiler='jFormsCompiler';
        $this->_compilerPath=JELIX_LIB_FORMS_PATH.'jFormsCompiler.class.php';

        parent::__construct($sel);
    }

    public function getClass(){
        return 'cForm_'.$this->module.'_'.$this->resource;
    }

}



/**
* sélecteurs de plugins
*/
class jSelectorPlugin implements jISelector {
    public $type = 'plug';
    public $plugin='';
    public $file = '';
    private $_valid;
    private $_path;

    function __construct($sel){
        global $gJConfig;
        if(preg_match("/^([\w\.]+)~([\w\.]+)$/", $sel, $m)){
            $this->plugin = $m[1];
            $this->file = $m[2];
            if(isset($gJConfig->_pluginsPathList[$this->plugin])){
                $this->_valid = true;
            }else{
                $this->_valid=false;
            }
        }else{
            $this->_valid=false;
        }
    }

    public function getPath (){
        global $gJConfig;
        if(isset($gJConfig->_pluginsPathList[$this->plugin])){
            return $gJConfig->_pluginsPathList[$this->plugin].$this->file;
        }else{
            return '';
        }
    }

    public function isValid(){
        return $this->_valid;
    }

    public function toString($full=false){
        if($full)
            return $this->type.':'.$this->plugin.'~'.$this->file;
        else
            return $this->plugin.'~'.$this->file;
    }

    public function getCompiler(){ return null;}
    public function useMultiSourceCompiler() { return false;}
    public function getCompiledFilePath (){ return '';}

}

/**
* sélecteur de fichier quelconque
*/
class jSelectorSimpleFile implements jISelector {
    public $type = 'simplefile';
    public $file = '';
    protected $_valid;
    protected $_path;
    protected $_basePath='';

    function __construct($sel){
        if(preg_match("/^([\w\.\/]+)$/", $sel, $m)){
            $this->file = $m[1];
            $this->_path = $this->_basePath.$m[1];
            $this->_valid = true;
        }else{
            $this->_valid = false;
        }
    }

    public function getPath (){
        return $this->_path;
    }

    public function isValid(){
        return $this->_valid;
    }

    public function toString($full=false){
        if($full)
            return $this->type.':'.$this->file;
        else
            return $this->file;
    }
    public function getCompiler(){ return null;}
    public function useMultiSourceCompiler() { return false;}
    public function getCompiledFilePath (){ return '';}
}

/**
 * sélecteur de fichier stocké dans le répertoire var
 */
class jSelectorVar extends jSelectorSimpleFile {
    public $type = 'var';
    function __construct($sel){
        $this->_basePath = JELIX_APP_VAR_PATH;
        parent::__construct($sel);
    }
}

/**
 * sélecteur de fichier stocké dans le répertoire config
 */
class jSelectorCfg extends jSelectorSimpleFile {
    public $type = 'cfg';
    function __construct($sel){
        $this->_basePath = JELIX_APP_CONFIG_PATH;
        parent::__construct($sel);
    }
}

/**
 * sélecteur de fichier stocké dans le répertoire temp
 */
class jSelectorTmp extends jSelectorSimpleFile {
    public $type = 'tmp';
    function __construct($sel){
        $this->_basePath = JELIX_APP_TEMP_PATH;
        parent::__construct($sel);
    }
}

/**
 * sélecteur de fichier stocké dans le répertoire  log
 */
class jSelectorLog extends jSelectorSimpleFile {
    public $type = 'log';
    function __construct($sel){
        $this->_basePath = JELIX_APP_LOG_PATH;
        parent::__construct($sel);
    }
}

/**
 * sélecteur de fichier stocké dans le répertoire lib
 */
class jSelectorLib extends jSelectorSimpleFile {
    public $type = 'lib';
    function __construct($sel){
        $this->_basePath = LIB_PATH;
        parent::__construct($sel);
    }
}

?>
