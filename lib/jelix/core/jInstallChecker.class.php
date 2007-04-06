<?php

/**
* check a jelix installation
*
* @package  jelix
* @subpackage core
* @author   Jouanneau Laurent
* @copyright 2007 Jouanneau laurent
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.0b2
*/

/**
 * interface for objects which output result of the install check
 * @since 1.0b2
 * @experimental
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
 * @since 1.0b2
 * @experimental
 */
class jInstallCheck {

    /**
     * the object responsible of the results output
     * @var jIInstallCheckReporter
     */
    protected $reporter;
    
    public $nbError = 0;
    public $nbOk = 0;
    public $nbWarning = 0;

    function __construct ($reporter){
        $this->reporter = $reporter;
    }

    function run(){
        $this->nbError = 0;
        $this->nbOk = 0;
        $this->nbWarning = 0;
        $this->reporter->start();
        try {
            $this->checkPhpExtensions();
            $this->checkAppPaths();
        }catch(Exception $e){
            $this->error('Cannot continue the checking : '.$e->getMessage());
        }
        $this->reporter->end($this);
    }

    protected function error($msg){
        if($this->reporter)
            $this->reporter->showError($msg);
        $this->nbError ++;
    }
    protected function ok($msg){
        if($this->reporter)
            $this->reporter->showOk($msg);
        $this->nbOk ++;
    }
    protected function warning($msg){
        if($this->reporter)
            $this->reporter->showWarning($msg);
        $this->nbWarning ++;
    }
    protected function notice($msg){
        if($this->reporter)
            $this->reporter->showNotice($msg);
    }

    function checkPhpExtensions(){
        $ok=true;

        if(!class_exists('DOMDocument',false)){
            $this->error('DOM extension is not installed');
            $ok=false;
        }
        if(!class_exists('DirectoryIterator',false)){
            $this->error('SPL extension is not installed');
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
                $this->error($name.' extension is not installed');
                $ok=false;
            }
        }

        if($ok)
            $this->ok('All needed PHP extensions are installed');

        return $ok;
    }


    function checkAppPaths(){
        $ok = true;
        if(!defined('JELIX_LIB_PATH') || !defined('JELIX_APP_PATH')){
            throw new Exception('jelix init.php file or application.ini.php file is not loaded');
        }

        if(!file_exists(JELIX_APP_TEMP_PATH) || !is_writable(JELIX_APP_TEMP_PATH)){
            $this->error('Temp directory is not writable or JELIX_APP_TEMP_PATH is not correctly set !');
            $ok=false;
        }
        if(!file_exists(JELIX_APP_LOG_PATH) || !is_writable(JELIX_APP_LOG_PATH)){
            $this->error('log directory is not writable or JELIX_APP_LOG_PATH is not correctly set!');
            $ok=false;
        }
        if(!file_exists(JELIX_APP_VAR_PATH)){
            $this->error('JELIX_APP_VAR_PATH is not correctly set: var directory  doesn\'t exist!');
            $ok=false;
        }
        if(!file_exists(JELIX_APP_CONFIG_PATH)){
            $this->error('JELIX_APP_CONFIG_PATH is not correctly set: config directory  doesn\'t exist!');
            $ok=false;
        }
        if(!file_exists(JELIX_APP_WWW_PATH)){
            $this->error('JELIX_APP_WWW_PATH is not correctly set: www directory  doesn\'t exist!');
            $ok=false;
        }
        if(!$ok)
            throw new Exception('jelix init.php file or application.ini.php file is not loaded');

        if($ok)
            $this->ok('temp, log, var, config and www directory are ok');

        if(!isset($GLOBALS['config_file']) || empty($GLOBALS['config_file']) || !file_exists(JELIX_APP_CONFIG_PATH.$GLOBALS['config_file'])){
            throw new Exception('$config_file does not exist or doesn\'t contain a correct application config file name');
        }

        return $ok;
    }



    function checkPhpSettings(){



    }


/*
existence des modules optionnels
    filter
    json
    xmlrpc
    jelix

Verification des settings de php.ini :

magic_quotes_gpc et magic_quotes_runtime doivent être à off. verifier que le plugin magicquotes soit activé ou pas selon la valeur
session.auto_start doit être à 0
safe_mode doit être à off
register_globals = off
asp_tags = off
short_open_tag = off


JELIX_LIB_PATH, JELIX_APP_PATH : si absent, erreur fatale.

Verifier que l'install est en adéquation avec les paramètres du fichier build :
PHP_VERSION_TARGET
ENABLE_PHP_FILTER
ENABLE_PHP_JSON
ENABLE_PHP_XMLRPC
ENABLE_PHP_JELIX

verifier la presence du module jelix

JELIX_APP_TEMP_PATH existe et est ok en écriture
JELIX_APP_LOG_PATH existe et est ok en écriture

existence des fichiers de configuration dans JELIX_APP_CONFIG_PATH

test connection à une base pour tout les profils


Dans la conf :
    verification de tous les chemins de modules
    verification de tous les chemins de plugins et plugins tpl

*/

}


?>