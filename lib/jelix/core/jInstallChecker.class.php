<?php
/**
* check a jelix installation
*
* @package  jelix
* @subpackage core
* @author   Laurent Jouanneau
* @contributor Bastien Jaillot
* @copyright 2007-2009 Laurent Jouanneau, 2008 Bastien Jaillot
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.0b2
*/

/**
 * interface for objects which output result of the install check
 * @package  jelix
 * @subpackage core
 * @since 1.0b2
 */
interface jIInstallCheckReporter {
    function start();
    function showError($message);
    function showWarning($message);
    function showOk($message);
    function showNotice($message);
    function end($checker);
}


/**
 * check an installation of a jelix application
 * @package  jelix
 * @subpackage core
 * @since 1.0b2
 */
class jInstallCheck {

    /**
     * the object responsible of the results output
     * @var jIInstallCheckReporter
     */
    protected $reporter;

    /**
     * @var jInstallerMessageProvider
     */
    public $messages;

    public $nbError = 0;
    public $nbOk = 0;
    public $nbWarning = 0;
    public $nbNotice = 0;

    protected $buildProperties;

    function __construct ($reporter, $lang=''){
        $this->reporter = $reporter;
        $this->messages = new jInstallerMessageProvider($lang);
#if STANDALONE_CHECKER
        $this->buildProperties = array(
#expand    'PHP_VERSION_TARGET'=>'__PHP_VERSION_TARGET__', 
#expand    'ENABLE_PHP_FILTER' =>'__ENABLE_PHP_FILTER__', 
#expand    'ENABLE_PHP_JSON'   =>'__ENABLE_PHP_JSON__', 
#expand    'ENABLE_PHP_JELIX'  =>'__ENABLE_PHP_JELIX__', 
#expand    'WITH_BYTECODE_CACHE'=>'__WITH_BYTECODE_CACHE__',
        );
#endif
    }

    function run(){
        $this->nbError = 0;
        $this->nbOk = 0;
        $this->nbWarning = 0;
        $this->nbNotice = 0;
        $this->reporter->start();
        try {
#ifnot STANDALONE_CHECKER
            $this->checkAppPaths();
            $this->loadBuildFile();
#endif
            $this->checkPhpExtensions();
            $this->checkPhpSettings();
        }catch(Exception $e){
            $this->reporter->showError($this->messages->get('cannot.continue').$e->getMessage());
            $this->nbError ++;
        }
        $this->reporter->end($this);
    }

    protected function error($msg){
        if($this->reporter)
            $this->reporter->showError($this->messages->get($msg));
        $this->nbError ++;
    }

    protected function ok($msg){
        if($this->reporter)
            $this->reporter->showOk($this->messages->get($msg));
        $this->nbOk ++;
    }
    /**
     * generate a warning
     * @param string $msg  the key of the message to display
     */
    protected function warning($msg){
        if($this->reporter)
            $this->reporter->showWarning($this->messages->get($msg));
        $this->nbWarning ++;
    }

    protected function notice($msg){
        if($this->reporter)
            $this->reporter->showNotice($this->messages->get($msg));
        $this->nbNotice ++;
    }

    function checkPhpExtensions(){
        $ok=true;
        if(!version_compare($this->buildProperties['PHP_VERSION_TARGET'], phpversion(), '<=')){
            $this->error('php.bad.version');
            $notice = $this->messages->get('php.version.required')
                     .$this->buildProperties['PHP_VERSION_TARGET'];
            $notice.= '. '.$this->messages->get('php.version.current').phpversion();
            $this->reporter->showNotice($notice);
            $ok=false;
        }
        if(!class_exists('DOMDocument',false)){
            $this->error('extension.dom');
            $ok=false;
        }
        if(!class_exists('DirectoryIterator',false)){
            $this->error('extension.spl');
            $ok=false;
        }

        $funcs=array(
            'simplexml_load_file'=>'simplexml',
            'preg_match'=>'pcre',
            'session_start'=>'session',
            'token_get_all'=>'tokenizer',
            'iconv_set_encoding'=>'iconv',
        );
        foreach($funcs as $f=>$name){
            if(!function_exists($f)){
                $this->error('extension.'.$name);
                $ok=false;
            }
        }
        if($this->buildProperties['ENABLE_PHP_FILTER'] == '1' && !extension_loaded ('filter')) {
            $this->error('extension.filter');
            $ok=false;
        }
        if($this->buildProperties['ENABLE_PHP_JSON'] == '1' && !extension_loaded ('json')) {
            $this->error('extension.json');
            $ok=false;
        }
        /*if($this->buildProperties['ENABLE_PHP_XMLRPC'] == '1' && !extension_loaded ('xmlrpc')) {
            $this->error('extension.xmlrpc');
            $ok=false;
        }*/
        if($this->buildProperties['ENABLE_PHP_JELIX'] == '1' && !extension_loaded ('jelix')) {
            $this->error('extension.jelix');
            $ok=false;
        }
        if($this->buildProperties['WITH_BYTECODE_CACHE'] != 'auto' &&
           $this->buildProperties['WITH_BYTECODE_CACHE'] != '') {
            if(!extension_loaded ('apc') && !extension_loaded ('eaccelerator') && !extension_loaded ('xcache')) {
                $this->error('extension.opcode.cache');
                $ok=false;
            }
        }
        if($ok)
            $this->ok('extensions.required.ok');

        return $ok;
    }
#ifnot STANDALONE_CHECKER
    function checkAppPaths(){
        $ok = true;
        if(!defined('JELIX_LIB_PATH') || !defined('JELIX_APP_PATH')){
            throw new Exception($this->messages->get('path.core'));
        }

        if(!file_exists(JELIX_APP_TEMP_PATH) || !is_writable(JELIX_APP_TEMP_PATH)){
            $this->error('path.temp');
            $ok=false;
        }
        if(!file_exists(JELIX_APP_LOG_PATH) || !is_writable(JELIX_APP_LOG_PATH)){
            $this->error('path.log');
            $ok=false;
        }
        if(!file_exists(JELIX_APP_VAR_PATH)){
            $this->error('path.var');
            $ok=false;
        }
        if(!file_exists(JELIX_APP_CONFIG_PATH)){
            $this->error('path.config');
            $ok=false;
        }
        if(!file_exists(JELIX_APP_WWW_PATH)){
            $this->error('path.www');
            $ok=false;
        }

        if($ok)
            $this->ok('paths.ok');
        else
            throw new Exception($this->messages->get('too.critical.error'));

        if(!isset($GLOBALS['config_file']) ||
           empty($GLOBALS['config_file']) ||
           !file_exists(JELIX_APP_CONFIG_PATH.$GLOBALS['config_file'])){
            throw new Exception($this->messages->get('config.file'));
        }

        return $ok;
    }

    function loadBuildFile() {
        if (!file_exists(JELIX_LIB_PATH.'BUILD')){
            throw new Exception($this->messages->get('build.not.found'));
        } else {
            $this->buildProperties = parse_ini_file(JELIX_LIB_PATH.'BUILD');
        }
    }
#endif

    function checkPhpSettings(){
        $ok = true;
#ifnot STANDALONE_CHECKER
        $defaultconfig = parse_ini_file(JELIX_APP_CONFIG_PATH."defaultconfig.ini.php", true);
        $indexconfig = parse_ini_file(JELIX_APP_CONFIG_PATH."index/config.ini.php", true);

        if ((isset ($defaultconfig['coordplugins']['magicquotes']) && $defaultconfig['coordplugins']['magicquotes'] == 1) ||
            (isset ($indexconfig['coordplugins']['magicquotes']) && $indexconfig['coordplugins']['magicquotes'] == 1)) {
            if(ini_get('magic_quotes_gpc') == 1){
                $this->notice('ini.magic_quotes_gpc_with_plugin');
            }
            else {
                $this->error('ini.magicquotes_plugin_without_php');
                $ok=false;
            }
        }
        else {
#endif
            if(ini_get('magic_quotes_gpc') == 1){
                $this->warning('ini.magic_quotes_gpc');
                $ok=false;
            }
#ifnot STANDALONE_CHECKER
        }
#endif
        if(ini_get('magic_quotes_runtime') == 1){
            $this->error('ini.magic_quotes_runtime');
            $ok=false;
        }

        if(ini_get('session.auto_start') == 1){
            $this->error('ini.session.auto_start');
            $ok=false;
        }

        if(ini_get('safe_mode') == 1){
            $this->warning('safe_mode');
            $ok=false;
        }

        if(ini_get('register_globals') == 1){
            $this->warning('ini.register_globals');
            $ok=false;
        }

        if(ini_get('asp_tags') == 1){
            $this->notice('ini.asp_tags');
        }
        if(ini_get('short_open_tag') == 1){
            $this->notice('ini.short_open_tag');
        }
        if($ok){
            $this->ok('ini.ok');
        }
        return $ok;
    }
}
