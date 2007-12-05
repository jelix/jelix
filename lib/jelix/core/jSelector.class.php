<?php
/**
* Declare all differents classes corresponding to main jelix selectors
*
* a selector is a string refering to a file or a ressource, by indicating its module and its name.
* For example : "moduleName~resourceName". There are several type of selector, depending on the
* resource type. Selector objects get the real path of the corresponding file, the name of the
* compiler (if the file has to be compile) etc.
* So here, there is a selector class for each selector type.
* @package     jelix
* @subpackage  core_selector
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @contributor Rahal
* @copyright   2005-2007 Laurent Jouanneau, 2007 Loic Mathaud, 2007 Rahal
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Create instance of selectors object
* @package    jelix
* @subpackage core_selector
*/
class jSelectorFactory {
    private function __construct(){}

    /**
     * Create an instance of a selector object corresponding to the given selector
     * @param string $selstr  the selector. It should be a full selector : "type:module~resource" (not "module~resource")
     * @return jISelector the corresponding selector
     */
    static public function create ($selstr){
        if(preg_match("/^([a-z]{3,5})\:([\w~\/\.]+)$/", $selstr, $m)){
            $cname='jSelector'.$m[1];
            if(class_exists($cname)){
                $sel = new $cname($m[2]);
                return $sel;
            }
        }
        throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($selstr,''));
    }
}
#ifnot ENABLE_PHP_JELIX
/**
 * interface of selector classes
 * @package    jelix
 * @subpackage core_selector
 */
interface jISelector {
    /**
     * @return string file path corresponding to the resource pointing by the selector
     */
    public function getPath ();
    /**
     * @return string file path of the compiled file (if the main file should be compiled by jelix)
     */
    public function getCompiledFilePath ();
    /**
     * @return jICompiler the compiler used to compile file
     */
    public function getCompiler();
    /**
     * @return boolean true if the compiler compile many file at one time
     */
    public function useMultiSourceCompiler();
    /**
     * @param boolean $full true if you want a full selector ("type:...")
     * @return string the selector
     */
    public function toString($full=false);
}
#endif

/**
 * Exception for selector errors
 * @package    jelix
 * @subpackage core_selector
 */
class jExceptionSelector extends jException { }

/**
 * base class for all selector concerning module files
 *
 * General syntax for them : "module~resource".
 * Syntax of resource depend on the selector type.
 * module is optional.
 * @package    jelix
 * @subpackage core_selector
 */
abstract class jSelectorModule implements jISelector {
    public $module = null;
    public $resource = null;

    protected $type = '_module';
    protected $_dirname='';
    protected $_suffix='';
    protected $_cacheSuffix='.php';
    protected $_path;
    protected $_cachePath;
    protected $_compiler = null;
    protected $_compilerPath;
    protected $_useMultiSourceCompiler=false;

    function __construct($sel){
#if ENABLE_PHP_JELIX
        if(jelix_scan_module_sel($sel, $this)){
            if($this->module ==''){
                $this->module = jContext::get ();
            }
#else
        if(preg_match("/^(([a-zA-Z0-9_\.]+)~)?([a-zA-Z0-9_\.]+)$/", $sel, $m)){
            if($m[1]!='' && $m[2]!=''){
                $this->module = $m[2];
            }else{
                $this->module = jContext::get ();
            }
            $this->resource = $m[3];
#endif
            $this->_createPath();
            $this->_createCachePath();
        }else{
            throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($sel,$this->type));
        }
    }

    public function getPath (){
        return $this->_path;
    }

    public function getCompiledFilePath (){
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
            throw new jExceptionSelector('jelix~errors.selector.module.unknow', $this->toString(true));
        }
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.$this->_suffix;
        if (!is_readable ($this->_path)){
            if($this->type == 'loc'){
                throw new Exception('(202) The file of the locale key "'.$this->toString().'" (charset '.$this->charset.', lang '.$this->locale.') does not exist');
            }elseif($this->toString() == 'jelix~errors.selector.invalid.target'){
                throw new Exception("Jelix Panic ! don't find localization files to show you an other error message !");
            }else{
                throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), $this->type));
            }
        }
    }

    protected function _createCachePath(){
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/'.$this->_dirname.$this->module.'~'.$this->resource.$this->_cacheSuffix;
    }
}

/**
 * Special Action selector for jcoordinator
 * Don't use it ! Only for internal purpose.
 * @internal
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorActFast extends jSelectorModule {
    protected $type = 'act';
    public $request = '';
    public $controller = '';
    public $method='';
    protected $_dirname='actions/';

    /**
     */
    function __construct($request, $module, $action){
        $this->module = $module;
        $r = explode('_',$action);
        if(count($r) == 1){
            $this->controller = 'default';
            $this->method = $r[0]==''?'index':$r[0];
        }else{
            $this->controller = $r[0]=='' ? 'default':$r[0];
            $this->method = $r[1]==''?'index':$r[1];
        }
        $this->resource = $this->controller.'_'.$this->method;

        $this->request = $request;
        $this->_createPath();
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            throw new jExceptionSelector('jelix~errors.selector.module.unknow', $this->toString());
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
        $className = $this->controller.'Ctrl';
#if ENABLE_OLD_CLASS_NAMING
        if(!class_exists($className,false)){
            if(class_exists('CT'.$this->controller,false))
                $className = 'CT'.$this->controller;
        }
#endif
        return $className;
    }

}


/**
 * Generic Action selector
 *
 * main syntax: "module~action@requestType". module should be a valid module name or # (#=says to get
 * the module of the current request). action should be an action name (controller_method).
 * all part are optional, but it should have one part at least.
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorAct extends jSelectorActFast {

    /**
     * @param string $sel  the selector
     * @param boolean $enableRequestPart true if the selector can contain the request part
     */
    function __construct($sel, $enableRequestPart = false){
        global $gJCoord;

#if ENABLE_PHP_JELIX
        if(jelix_scan_action_sel($sel, $this, $gJCoord->actionName)){
            if($this->module == '#'){
                $this->module = $gJCoord->moduleName;
            }elseif($this->module ==''){
                $this->module = jContext::get ();
            }

            if($this->request == '')
                $this->request = $gJCoord->request->type;

#else
        if(preg_match("/^(?:([a-zA-Z0-9_\.]+|\#)~)?([a-zA-Z0-9_]+|\#)?(?:@([a-zA-Z0-9_]+))?$/", $sel, $m)){
            $m=array_pad($m,4,'');
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
            $this->resource = $this->controller.'_'.$this->method;
            if($m[3] != '' && $enableRequestPart)
                $this->request = $m[3];
            else
                $this->request = $gJCoord->request->type;
#endif
            $this->_createPath();
        }else{
            throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($sel,$this->type));
        }
    }
}

/**
 * selector for business class
 *
 * business class is a class stored in classname.class.php file in the classes/ module directory
 * or one of its subdirectory.
 * syntax : "module~classname" or "module~classname.
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorClass extends jSelectorModule {
    protected $type = 'class';
    protected $_dirname = 'classes/';
    protected $_suffix = '.class.php';
    
    /**
    * subpath part in the resource content
    * @since 1.0b2
    */
    public $subpath ='';
    /** 
    * the class name specified in the selector
    * @since 1.0b2
    */
    public $className = '';

    function __construct($sel){
#if ENABLE_PHP_JELIX
        if(jelix_scan_class_sel($sel, $this)){
            if($this->module ==''){
                $this->module = jContext::get ();
            }
#else
        if(preg_match("/^(([a-zA-Z0-9_\.]+)~)?([a-zA-Z0-9_\.\\/]+)$/", $sel, $m)){
            if($m[1]!='' && $m[2]!=''){
                $this->module = $m[2];
            }else{
                $this->module = jContext::get ();
            }
            $this->resource = $m[3];
            if( ($p=strrpos($m[3], '/')) !== false){
                $this->className = substr($m[3],$p+1);
                $this->subpath = substr($m[3],0,$p+1);
            }else{
                $this->className = $m[3];
                $this->subpath ='';
            }
#endif
            $this->_createPath();
            $this->_createCachePath();
        }else{
            throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($sel,$this->type));
        }
    }

    protected function _createPath(){
        global $gJConfig;
        if (!isset($gJConfig->_modulesPathList[$this->module])) {
            throw new jExceptionSelector('jelix~errors.selector.module.unknow', $this->toString());
        } 
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->subpath.$this->className.$this->_suffix;

        if (!file_exists($this->_path) || strpos($this->subpath,'..') !== false ) { // second test for security issues
            throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), $this->type));
        }
    }

    protected function _createCachePath(){
        $this->_cachePath = '';
    }

    public function toString($full=false){
        if($full)
            return $this->type.':'.$this->module.'~'.$this->subpath.$this->className;
        else
            return $this->module.'~'.$this->subpath.$this->className;
    }
}

/**
 * selector for business class
 *
 * business class is a class stored in classname.class.php file in the classes/ module directory
 * or one of its subdirectory.
 * syntax : "module~classname" or "module~classname.
 * @package    jelix
 * @subpackage core_selector
 * @since 1.0b2
 */
class jSelectorInterface extends jSelectorClass {
    protected $type = 'iface';
    protected $_dirname = 'classes/';
    protected $_suffix = '.iface.php';
}

/**
 * selector for localisation string
 *
 * localisation string are stored in file properties.
 * syntax : "module~prefixFile.keyString".
 * Corresponding file : locales/xx_XX/prefixFile.CCC.properties.
 * xx_XX and CCC are lang and charset set in the configuration
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorLoc extends jSelectorModule {
    protected $type = 'loc';
    public $fileKey = '';
    public $messageKey = '';
    public $locale ='';
    public $charset='';
    public $_compiler = 'jLocalesCompiler';
    protected $_where;

    function __construct($sel, $locale=null, $charset=null){
        global $gJConfig;
        if ($locale === null){
            $locale = $gJConfig->locale;
        }
        if ($charset === null){
            $charset = $gJConfig->charset;
        }
        if(strpos($locale,'_') === false){
            $locale.='_'.strtoupper($locale);
        }
        $this->locale = $locale;
        $this->charset = $charset;
        $this->_dirname =  'locales/' .$locale.'/';
        $this->_suffix = '.'.$charset.'.properties';
        $this->_cacheSuffix = '.'.$charset.'.php';
        $this->_compilerPath=JELIX_LIB_CORE_PATH.'jLocalesCompiler.class.php';

#if ENABLE_PHP_JELIX
        if(jelix_scan_locale_sel($sel, $this)){
            if($this->module ==''){
                $this->module = jContext::get ();
            }
#else
        if(preg_match("/^(([a-zA-Z0-9_\.]+)~)?([a-zA-Z0-9_]+)\.([a-zA-Z0-9_\.]+)$/", $sel, $m)){
            if($m[1]!='' && $m[2]!=''){
                $this->module = $m[2];
            }else{
                $this->module = jContext::get ();
            }
            $this->resource = $m[3];
            $this->fileKey = $m[3];
            $this->messageKey = $m[4];
#endif
            $this->_createPath();
            $this->_createCachePath();
        }else{
            throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($sel,$this->type));
        }
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            throw new jExceptionSelector('jelix~errors.selector.module.unknow', $this->toString());
        }

        // on regarde si la locale a été redéfini
        $overloadedPath = JELIX_APP_VAR_PATH.'overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix;
        if (is_readable ($overloadedPath)){
           $this->_path = $overloadedPath;
           $this->_where = 'overloaded/';
           return;
        }
        // et sinon, on regarde si la locale existe dans le module en question
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.$this->_suffix;

        if (!is_readable ($this->_path)){
            throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), "locale"));
        }
        $this->_where = 'modules/';
    }

    protected function _createCachePath(){
        // on ne partage pas le même cache pour tous les emplacements possibles
        // au cas où un overload était supprimé
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/locales/'.$this->_where.$this->module.'~'.$this->resource.$this->_cacheSuffix;
    }

    public function toString($full=false){
        if($full)
            return $this->type.':'.$this->module.'~'.$this->fileKey.'.'.$this->messageKey;
        else
            return $this->module.'~'.$this->fileKey.'.'.$this->messageKey;
    }
}

/**
 * Selector for dao file
 * syntax : "module~daoName".
 * file : daos/daoName.dao.xml
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorDao extends jSelectorModule {
    protected $type = 'dao';
    public $driver;
    protected $_dirname = 'daos/';
    protected $_suffix = '.dao.xml';
    protected $_where;

    function __construct($sel, $driver, $isprofil=true){
        if($isprofil){
            $p = jDb::getProfil($driver);
            if($p['driver'] == 'pdo'){
                $this->driver=substr($p['dsn'],0,strpos($p['dsn'],':'));
            }else{
                $this->driver= $p['driver'];
            }
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
            throw new jExceptionSelector('jelix~errors.selector.module.unknow', $this->toString());
        }

        // on regarde si le dao a été redéfini
        $overloadedPath = JELIX_APP_VAR_PATH.'overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix;
        if (is_readable ($overloadedPath)){
           $this->_path = $overloadedPath;
           $this->_where = 'overloaded/';
           return;
        }
        // et sinon, on regarde si le dao existe dans le module en question
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.$this->_suffix;

        if (!is_readable ($this->_path)){
            throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), "dao"));
        }
        $this->_where = 'modules/';
    }

    protected function _createCachePath(){
        // on ne partage pas le même cache pour tous les emplacements possibles
        // au cas où un overload était supprimé
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/daos/'.$this->_where.$this->module.'~'.$this->resource.'~'.$this->driver.$this->_cacheSuffix;
    }

    public function getDaoClass(){
        return 'cDao_'.$this->module.'_Jx_'.$this->resource.'_Jx_'.$this->driver;
    }
    public function getDaoRecordClass(){
        return 'cDaoRecord_'.$this->module.'_Jx_'.$this->resource.'_Jx_'.$this->driver;
    }
}

/**
 * Template selector
 *
 * syntax : "module~tplName".
 * file : templates/tplName.tpl .
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorTpl extends jSelectorModule {
    protected $type = 'tpl';
    protected $_dirname = 'templates/';
    protected $_suffix = '.tpl';
    protected $_where;
    public $outputType='';
    public $trusted=true;

    /**
     * @param string $sel the template selector
     * @param string $outputtype  the type of output (html, text..) By default, it takes the response type
     * @param boolean $trusted  says if the template file is trusted or not
     */
    function __construct($sel, $outputtype='', $trusted=true){
        if($outputtype == '')
            $this->outputType = $GLOBALS['gJCoord']->response->getFormatType();
        else
            $this->outputType = $outputtype;
        $this->trusted = $trusted;
        $this->_compiler='jTplCompiler';
        $this->_compilerPath=JELIX_LIB_TPL_PATH.'jTplCompiler.class.php';
        parent::__construct($sel);
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            throw new jExceptionSelector('jelix~errors.selector.module.unknow', $this->toString());
        }

        $path = $this->module.'/'.$this->resource;
        $lpath = $this->module.'/'.$gJConfig->locale.'/'.$this->resource;

        if($gJConfig->theme != 'default'){
            // on regarde si il y a un template redéfinie pour le theme courant
            $this->_where = 'themes/'.$gJConfig->theme.'/'.$lpath;
            $this->_path = JELIX_APP_VAR_PATH.$this->_where.'.tpl';
            if (is_readable ($this->_path)){
                return;
            }
            // on regarde si il y a un template redéfinie pour le theme courant
            $this->_where = 'themes/'.$gJConfig->theme.'/'.$path;
            $this->_path = JELIX_APP_VAR_PATH.$this->_where.'.tpl';
            if (is_readable ($this->_path)){
                return;
            }
        }

        // on regarde si il y a un template redéfinie dans le theme par defaut
        $this->_where = 'themes/default/'.$lpath;
        $this->_path = JELIX_APP_VAR_PATH.$this->_where.'.tpl';
        if (is_readable ($this->_path)){
            return;
        }

        $this->_where = 'themes/default/'.$path;
        $this->_path = JELIX_APP_VAR_PATH.$this->_where.'.tpl';
        if (is_readable ($this->_path)){
            return;
        }

        // et sinon, on regarde si le template existe dans le module en question
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$gJConfig->locale.'/'.$this->resource.'.tpl';
        if (is_readable ($this->_path)){
            $this->_where = 'modules/'.$lpath;
            return;
        }

        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.'.tpl';
        if (is_readable ($this->_path)){
            $this->_where = 'modules/'.$path;
            return;
        }

        throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), "template"));

    }

    protected function _createCachePath(){
       // on ne partage pas le même cache pour tous les emplacements possibles
       // au cas où un overload était supprimé
       $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/templates/'.$this->_where.'_'.$this->outputType.($this->trusted?'_t':'').$this->_cacheSuffix;
    }
}

/**
 * Zone selector
 *
 * syntax : "module~zoneName".
 * file : zones/zoneName.zone.php .
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorZone extends jSelectorModule {
    protected $type = 'zone';
    protected $_dirname = 'zones/';
    protected $_suffix = '.zone.php';

    protected function _createCachePath(){
        $this->_cachePath = '';
    }
}

/**
 * Form selector
 *
 * syntax : "module~formName".
 * file : forms/formName.form.xml .
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorForm extends jSelectorModule {
    protected $type = 'form';
    protected $_where;
    protected $_dirname = 'forms/';
    protected $_suffix = '.form.xml';
    
    function __construct($sel){

        $this->_compiler='jFormsCompiler';
        $this->_compilerPath=JELIX_LIB_FORMS_PATH.'jFormsCompiler.class.php';

        parent::__construct($sel);
    }

    public function getClass(){
        return 'cForm_'.$this->module.'_Jx_'.$this->resource;
    }

   
    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            throw new jExceptionSelector('jelix~errors.selector.module.unknow', $this->toString(true));
        }
        
        // we see if the forms have been redefined
        $overloadedPath = JELIX_APP_VAR_PATH.'overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix;
        if (is_readable ($overloadedPath)){
           $this->_path = $overloadedPath;
           $this->_where = 'overloaded/';
           return;
        }

        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.$this->_suffix;
        if (!is_readable ($this->_path)){
            throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), $this->type));
        }
        $this->_where = 'modules/';
    }
    
    protected function _createCachePath(){
        // on ne partage pas le même cache pour tous les emplacements possibles
        // au cas où un overload était supprimé
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/'.$this->_dirname.$this->_where.$this->module.'~'.$this->resource.$this->_cacheSuffix;
    }
    
    public function getCompiledBuilderFilePath ($type){
        return JELIX_APP_TEMP_PATH.'compiled/'.$this->_dirname.$this->_where.$this->module.'~'.$this->resource.'_builder_'.$type.$this->_cacheSuffix;
    }
    
}

/**
 * base class for simple file selector
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorSimpleFile implements jISelector {
    protected $type = 'simplefile';
    public $file = '';
    protected $_path;
    protected $_basePath='';

    function __construct($sel){
        if(preg_match("/^([\w\.\/]+)$/", $sel, $m)){
            $this->file = $m[1];
            $this->_path = $this->_basePath.$m[1];
        }else{
            throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($sel,$this->type));
        }
    }

    public function getPath (){
        return $this->_path;
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
 * Selector for files stored in the var directory
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorVar extends jSelectorSimpleFile {
    protected $type = 'var';
    function __construct($sel){
        $this->_basePath = JELIX_APP_VAR_PATH;
        parent::__construct($sel);
    }
}

/**
 * Selector for files stored in the config directory
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorCfg extends jSelectorSimpleFile {
    protected $type = 'cfg';
    function __construct($sel){
        $this->_basePath = JELIX_APP_CONFIG_PATH;
        parent::__construct($sel);
    }
}

/**
 * Selector for files stored in the temp directory
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorTmp extends jSelectorSimpleFile {
    protected $type = 'tmp';
    function __construct($sel){
        $this->_basePath = JELIX_APP_TEMP_PATH;
        parent::__construct($sel);
    }
}

/**
 * Selector for files stored in the log directory
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorLog extends jSelectorSimpleFile {
    protected $type = 'log';
    function __construct($sel){
        $this->_basePath = JELIX_APP_LOG_PATH;
        parent::__construct($sel);
    }
}

/**
 * Selector for files stored in the lib directory
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorLib extends jSelectorSimpleFile {
    protected $type = 'lib';
    function __construct($sel){
        $this->_basePath = LIB_PATH;
        parent::__construct($sel);
    }
}

?>