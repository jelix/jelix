<?php
/**
* check a jelix installation
*
* @package  jelix
* @subpackage core
* @author   Jouanneau Laurent
* @contributor Bastien Jaillot
* @copyright 2007-2008 Jouanneau laurent, 2008 Bastien Jaillot
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
 * message provider for jInstallCheck
 * @package  jelix
 * @subpackage core
 * @since 1.0b2
 */
class jInstallMessageProvider {
    protected $currentLang;

    protected $messages = array(
        'fr'=>array(
            'checker.title'=>'Vérification de l\'installation de Jelix',
            'number.errors'=>' erreurs.',
            'number.error'=>' erreur.',
            'number.warnings'=>' avertissements.',
            'number.warning'=>' avertissement.',
            'number.notices'=>' remarques.',
            'number.notice'=>' remarque.',
            'build.not.found'=>'Le fichier BUILD de jelix est introuvable',
            'conclusion.error'=>'Vous devez corriger l\'erreur pour faire fonctionner correctement votre application.',
            'conclusion.errors'=>'Vous devez corriger les erreurs pour faire fonctionner correctement votre application.',
            'conclusion.warning'=>'Votre application peut à priori fonctionner, mais il est préférable de corriger l\'avertissement pour être sûr.',
            'conclusion.warnings'=>'Votre application peut à priori fonctionner, mais il est préférable de corriger les avertissements pour être sûr.',
            'conclusion.notice'=>'L\'installation est correcte malgré la remarque.',
            'conclusion.notices'=>'L\'installation est correcte malgré les remarques.',
            'conclusion.ok'=>'L\'installation est correcte',
            'cannot.continue'=>'Les vérifications ne peuvent continuer : ',
            'extension.dom'=>'L\'extension DOM n\'est pas installée',
            'extension.spl'=>'L\'extension spl  n\'est pas installée',
            'extension.simplexml'=>'L\'extension simplexml n\'est pas installée',
            'extension.pcre'=>'L\'extension pcre n\'est pas installée',
            'extension.session'=>'L\'extension session n\'est pas installée',
            'extension.tokenizer'=>'L\'extension tokenizer n\'est pas installée',
            'extension.iconv'=>'L\'extension iconv n\'est pas installée',
            'extensions.required.ok'=>'Toutes les extensions obligatoires sont installées',
            'extension.filter'=>'Cette édition de Jelix a besoin de l\'extension filter',
            'extension.json'=>'Cette édition de Jelix a besoin de l\'extension json',
            'extension.xmlrpc'=>'Cette édition de Jelix a besoin de l\'extension xmlrpc',
            'extension.jelix'=>'Cette édition de Jelix a besoin de l\'extension jelix',
            'extension.apc'=>'Cette édition de Jelix a besoin de l\'extension apc',
            'extension.eaccelerator'=>'Cette édition de Jelix a besoin de l\'extension eaccelerator',
            'path.core'=>'Le fichier init.php  de jelix ou le fichier application.ini.php de votre application n\'est pas chargé',
            'path.temp'=>'Le repertoire temporaire n\'est pas accessible en écriture ou alors JELIX_APP_TEMP_PATH n\'est pas configurée comme il faut',
            'path.log'=>'Le repertoire var/log dans votre application n\'est pas accessible en écriture ou alors JELIX_APP_LOG_PATH n\'est pas configurée comme il faut',
            'path.var'=>'JELIX_APP_VAR_PATH n\'est pas configuré correctement : ce répertoire n\'existe pas',
            'path.config'=>'JELIX_APP_CONFIG_PATH n\'est pas configuré correctement : ce répertoire n\'existe pas',
            'path.www'=>'JELIX_APP_WWW_PATH n\'est pas configuré correctement : ce répertoire n\'existe pas',
            'php.bad.version'=>'Mauvaise version de PHP',
            'php.version.current'=>'Version courante :',
            'php.version.required'=>'Cette édition de Jelix nécessite au moins PHP ',
            'too.critical.error'=>'Trop d\'erreurs critiques sont apparues. Corrigez les.',
            'config.file'=>'La variable $config_file n\'existe pas ou le fichier qu\'elle indique n\'existe pas',
            'paths.ok'=>'Les répertoires temp, log, var, config et www sont ok',
            'ini.magic_quotes_gpc_with_plugin'=>'php.ini : le plugin magicquotes est activé mais vous devriez mettre magic_quotes_gpc à off',
            'ini.magicquotes_plugin_without_php'=>'php.ini : le plugin magicquotes est activé alors que magic_quotes_gpc est déjà à off, désactivez le plugin',
            'ini.magic_quotes_gpc'=>'php.ini : l\'activation des magicquotes n\'est pas recommandée pour jelix. Vous devez les désactiver ou activer le plugin magicquotes si ce n\'est pas fait',
            'ini.magic_quotes_runtime'=>'php.ini : magic_quotes_runtime doit être à off',
            'ini.session.auto_start'=>'php.ini : session.auto_start doit être à off',
            'ini.safe_mode'=>'php.ini : le safe_mode n\'est pas recommandé pour jelix.',
            'ini.register_globals'=>'php.ini : il faut désactiver register_globals, pour des raisons de sécurité et parce que Jelix n\'en a pas besoin.',
            'ini.asp_tags'=>'php.ini :  il est conseillé de désactiver asp_tags. Jelix n\'en a pas besoin.',
            'ini.short_open_tag'=>'php.ini :  il est conseillé de désactiver short_open_tag. Jelix n\'en a pas besoin.',
            'ini.ok'=>'Les paramètres de php sont ok',
        ),

        'en'=>array(
            'checker.title'=>'Jelix Installation checking',
            'number.errors'=>' errors.',
            'number.error'=>' error.',
            'number.warnings'=>' warnings.',
            'number.warning'=>' warning.',
            'number.notices'=>' notices.',
            'number.notice'=>' notice.',
            'build.not.found'=>'BUILD jelix file is not found',
            'conclusion.error'=>'You must fix the error in order to run your application correctly.',
            'conclusion.errors'=>'You must fix errors in order to run your application correctly.',
            'conclusion.warning'=>'Your application may run without problems, but it is recommanded to fix the warning.',
            'conclusion.warnings'=>'Your application may run without problems, but it is recommanded to fix warnings.',
            'conclusion.notice'=>'The installation is ok, although there is a notice.',
            'conclusion.notices'=>'The installation is ok, although there are notices.',
            'conclusion.ok'=>'The installation is ok',
            'cannot.continue'=>'Cannot continue the checking: ',
            'extension.dom'=>'DOM extension is not installed',
            'extension.spl'=>'SPL extension is not installed',
            'extension.simplexml'=>'simplexml extension is not installed',
            'extension.pcre'=>'pcre extension is not installed',
            'extension.session'=>'session extension is not installed',
            'extension.tokenizer'=>'tokenizer extension is not installed',
            'extension.iconv'=>'iconv extension is not installed',
            'extensions.required.ok'=>'All needed PHP extensions are installed',
            'extension.filter'=>'This Jelix edition require the xmlrpc extension',
            'extension.json'=>'This Jelix edition require the json extension',
            'extension.xmlrpc'=>'This Jelix edition require the xmlrpc extension',
            'extension.jelix'=>'This Jelix edition require the jelix extension',
            'extension.apc'=>'This Jelix edition require the apc extension',
            'extension.eaccelerator'=>'This Jelix edition require the eaccelerator extension',
            'path.core'=>'jelix init.php file or application.ini.php file is not loaded',
            'path.temp'=>'temp/yourApp directory is not writable or JELIX_APP_TEMP_PATH is not correctly set !',
            'path.log'=>'var/log directory (in the directory of your application) is not writable or JELIX_APP_LOG_PATH is not correctly set!',
            'path.var'=>'JELIX_APP_VAR_PATH is not correctly set: var directory  doesn\'t exist!',
            'path.config'=>'JELIX_APP_CONFIG_PATH is not correctly set: config directory  doesn\'t exist!',
            'path.www'=>'JELIX_APP_WWW_PATH is not correctly set: www directory  doesn\'t exist!',
            'php.bad.version'=>'Bad PHP version',
            'php.version.current'=>'Current version:',
            'php.version.required'=>'This edition of Jelix require at least PHP ',
            'too.critical.error'=>'Too much critical errors. Fix them.',
            'config.file'=>'$config_file variable does not exist or doesn\'t contain a correct application config file name',
            'paths.ok'=>'temp, log, var, config and www directory are ok',
            'ini.magic_quotes_gpc_with_plugin'=>'php.ini : the magicquotes plugin is actived but you should set magic_quotes_gpc to off',
            'ini.magicquotes_plugin_without_php'=>'php.ini : the magicquotes plugin is actived whereas magic_quotes_gpc is already off, you should disable the plugin',
            'ini.magic_quotes_gpc'=>'php.ini : magicquotes are not recommended for Jelix. You should deactivate it or activate the magicquote jelix plugin',
            'ini.magic_quotes_runtime'=>'php.ini : magic_quotes_runtime must be off',
            'ini.session.auto_start'=>'php.ini : session.auto_start must be off',
            'ini.safe_mode'=>'php.ini : safe_mode is not recommended.',
            'ini.register_globals'=>'php.ini : you must deactivate register_globals, for security reasons, and because Jelix doesn\'t need it.',
            'ini.asp_tags'=>'php.ini :  you should deactivate  asp_tags. Jelix doesn\'t need it.',
            'ini.short_open_tag'=>'php.ini :  you should deactivate short_open_tag. Jelix doesn\'t need it.',
            'ini.ok'=>'php settings are ok',
           /* ''=>'',*/
        ),
    );

    function __construct($lang=''){
        if($lang == ''){
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach($languages as $bl){
                // pour les user-agents qui livrent un code internationnal
                if(preg_match("/^([a-zA-Z]{2})(?:[-_]([a-zA-Z]{2}))?(;q=[0-9]\\.[0-9])?$/",$bl,$match)){
                    $lang = strtolower($match[1]);
                    break;
                }
            }
        }elseif(preg_match("/^([a-zA-Z]{2})(?:[-_]([a-zA-Z]{2}))?$/",$lang,$match)){
            $lang = strtolower($match[1]);
        }
        if(!isset($this->messages[$lang])){
            $lang = 'en';
        }
        $this->currentLang = $lang;
    }

    function get($key){
        if(isset($this->messages[$this->currentLang][$key])){
            return $this->messages[$this->currentLang][$key];
        }else{
            throw new Exception ("Error : don't find error message '$key'");
        }
    }

    function getLang(){
        return $this->currentLang;
    }
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
     * @var JInstallMessageProvider
     */
    public $messages;

    public $nbError = 0;
    public $nbOk = 0;
    public $nbWarning = 0;
    public $nbNotice = 0;

    protected $buildProperties;

    function __construct ($reporter, $lang=''){
        $this->reporter = $reporter;
        $this->messages = new jInstallMessageProvider($lang);
    }

    function run(){
        $this->nbError = 0;
        $this->nbOk = 0;
        $this->nbWarning = 0;
        $this->nbNotice = 0;
        $this->reporter->start();
        try {
            $this->checkAppPaths();
            $this->loadBuildFile();
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
            $notice = $this->messages->get('php.version.required').$this->buildProperties['PHP_VERSION_TARGET'];
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
        if($this->buildProperties['WITH_BYTECODE_CACHE'] == 'apc' && !extension_loaded ('apc')) {
            $this->error('extension.apc');
            $ok=false;
        }
        if($this->buildProperties['WITH_BYTECODE_CACHE'] == 'eaccelerator' && !extension_loaded ('eaccelerator')) {
            $this->error('extension.eaccelerator');
            $ok=false;
        }

        if($ok)
            $this->ok('extensions.required.ok');

        return $ok;
    }

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

        if(!isset($GLOBALS['config_file']) || empty($GLOBALS['config_file']) || !file_exists(JELIX_APP_CONFIG_PATH.$GLOBALS['config_file'])){
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

    function checkPhpSettings(){
        $ok = true;
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
            if(ini_get('magic_quotes_gpc') == 1){
                $this->warning('ini.magic_quotes_gpc');
                $ok=false;
            }
        }
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
?>
