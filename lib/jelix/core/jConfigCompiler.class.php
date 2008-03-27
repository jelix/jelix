<?php
/**
* @package      jelix
* @subpackage   core
* @author       Jouanneau Laurent
* @contributor  Thibault PIRONT < nuKs >
* @copyright    2006-2008 Jouanneau laurent
* @copyright    2007 Thibault PIRONT
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * jConfigCompiler merge two ini file in a single array and store it in a temporary file
 * This is a static class
 * @package  jelix
 * @subpackage core
 * @static
 */
class jConfigCompiler {

    private function __construct (){ }

    /**
     * read the given ini file. Merge it with the content of defaultconfig.ini.php
     * It also calculates some options. It stores the result in a temporary file
     * @param string $configFile the config file name
     * @return object an object which contains configuration values
     */
    static public function read($configFile){

#if ENABLE_PHP_JELIX
        $config = jelix_read_ini(JELIX_LIB_CORE_PATH.'defaultconfig.ini.php');

        @jelix_read_ini(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',$config);

        if($configFile !='defaultconfig.ini.php'){
            if(!file_exists(JELIX_APP_CONFIG_PATH.$configFile))
                die("Jelix config file $configFile is missing !");
            if( false === @jelix_read_ini(JELIX_APP_CONFIG_PATH.$configFile,$config))
                die("Syntax error in the Jelix config file $configFile !");
        }
        $config->isWindows = (DIRECTORY_SEPARATOR == '\\');
        if(trim( $config->startAction) == '') {
            $config->startAction = ':';
        }

        $config->_allBasePath = array();

        $config->_modulesPathList = self::_loadPathList($config->modulesPath, $config->_allBasePath);

        self::_loadPluginsPathList($config);

        if($config->checkTrustedModules){
            $config->_trustedModules = explode(',',$config->trustedModules);
        }else{
            $config->_trustedModules = array_keys($config->_modulesPathList);
        }

        if($config->urlengine['scriptNameServerVariable'] == '') {
            $config->urlengine['scriptNameServerVariable'] = self::_findServerName($config->urlengine['entrypointExtension']);
        }

        $path=$config->urlengine['basePath'];
        if($path!='/' && $path!=''){
            if($path{0} != '/') $path='/'.$path;
            if(substr($path,-1) != '/') $path.='/';
            $config->urlengine['basePath'] = $path;
        }

        if($path!='' && $config->urlengine['jelixWWWPath']{0} != '/')
            $config->urlengine['jelixWWWPath'] = $path.$config->urlengine['jelixWWWPath'];

        self::_initResponsesPath($config->responses);
        self::_initResponsesPath($config->_coreResponses);


        /*if(preg_match("/^([a-zA-Z]{2})(?:_([a-zA-Z]{2}))?$/",$config->locale,$m)){
            if(!isset($m[2])){
                $m[2] = $m[1];
            }
            $config->defaultLang = strtolower($m[1]);
            $config->defaultCountry = strtoupper($m[2]);
            $config->locale = $config->defaultLang.'_'.$config->defaultCountry;
        }else{
            die("Syntax error in the locale parameter in Jelix config file $configFile !");
        }*/
#else
        $config = jIniFile::read(JELIX_LIB_CORE_PATH.'defaultconfig.ini.php');

        if( $commonConfig = parse_ini_file(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',true)){
            self::_mergeConfig($config, $commonConfig);
        }

        if($configFile !='defaultconfig.ini.php'){
            if(!file_exists(JELIX_APP_CONFIG_PATH.$configFile))
                die("Jelix config file $configFile is missing !");
            if( false === ($userConfig = parse_ini_file(JELIX_APP_CONFIG_PATH.$configFile,true)))
                die("Syntax error in the Jelix config file $configFile !");
            self::_mergeConfig($config, $userConfig);
        }
        $config['isWindows'] =  (DIRECTORY_SEPARATOR == '\\');
        if(trim( $config['startAction']) == '') {
            $config['startAction'] = ':';
        }

        $config['_allBasePath'] = array();
        $config['_modulesPathList'] = self::_loadPathList($config['modulesPath'], $config['_allBasePath']);

        self::_loadPluginsPathList($config);

        if($config['checkTrustedModules']){
            $config['_trustedModules'] = explode(',',$config['trustedModules']);
        }else{
            $config['_trustedModules'] = array_keys($config['_modulesPathList']);
        }

        if($config['urlengine']['scriptNameServerVariable'] == '') {
            $config['urlengine']['scriptNameServerVariable'] = self::_findServerName($config['urlengine']['entrypointExtension']);
        }

        $path=$config['urlengine']['basePath'];
        if($path!='/' && $path!=''){
            if($path{0} != '/') $path='/'.$path;
            if(substr($path,-1) != '/') $path.='/';
            $config['urlengine']['basePath'] = $path;
        }

        if($path!='' && $config['urlengine']['jelixWWWPath']{0} != '/')
            $config['urlengine']['jelixWWWPath'] = $path.$config['urlengine']['jelixWWWPath'];

        self::_initResponsesPath($config['responses']);
        self::_initResponsesPath($config['_coreResponses']);

        /*if(preg_match("/^([a-zA-Z]{2})(?:_([a-zA-Z]{2}))?$/",$config['locale'],$m)){
            if(!isset($m[2])){
                $m[2] = $m[1];
            }
            $config['defaultLang'] = strtolower($m[1]);
            $config['defaultCountry'] = strtoupper($m[2]);
            $config['locale'] = $config['defaultLang'].'_'.$config['defaultCountry'];
        }else{
            die("Syntax error in the locale parameter in Jelix config file $configFile !");
        }*/
#endif

#if WITH_BYTECODE_CACHE == 'auto'
        if(BYTECODE_CACHE_EXISTS){
            $filename=JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.conf.php';
            if ($f = @fopen($filename, 'wb')) {
#if ENABLE_PHP_JELIX
                fwrite($f, '<?php $config = '.var_export(get_object_vars($config),true).";\n?>");
#else
                fwrite($f, '<?php $config = '.var_export($config,true).";\n?>");
#endif
                fclose($f);
            } else {
                throw new Exception('(24)Error while writing config cache file '.$filename);
            }
        }else{
#if ENABLE_PHP_JELIX
            jIniFile::write(get_object_vars($config), JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.resultini.php', ";<?php die('');?>\n");
#else
            jIniFile::write($config, JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.resultini.php', ";<?php die('');?>\n");
#endif
        }
#elseif WITH_BYTECODE_CACHE 
        $filename=JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.conf.php';
        if ($f = @fopen($filename, 'wb')) {
#if ENABLE_PHP_JELIX
            fwrite($f, '<?php $config = '.var_export(get_object_vars($config),true).";\n?>");
#else
            fwrite($f, '<?php $config = '.var_export($config,true).";\n?>");
#endif
            fclose($f);
        } else {
            throw new Exception('(24)Error while writing config cache file '.$filename);
        }
#else
#if ENABLE_PHP_JELIX
        jIniFile::write(get_object_vars($config), JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.resultini.php', ";<?php die('');?>\n");
#else
        jIniFile::write($config, JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.resultini.php', ";<?php die('');?>\n");
#endif
#endif
#ifnot ENABLE_PHP_JELIX
        $config = (object) $config;
#endif
        return $config;
    }

    /**
     * Analyse and check the "lib:" and "app:" path.
     * @param array $list list of "lib:*" and "app:*" path
     * @return array list of full path
     */
    static private function _loadPathList($list, &$allBasePath){
        $list = split(' *, *',$list);
        array_unshift($list, JELIX_LIB_PATH.'core-modules/');
        $result=array();
        foreach($list as $k=>$path){
            if(trim($path) == '') continue;
            $p = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
            if(!file_exists($p)){
                trigger_error('The path, '.$path.' given in the jelix config, doesn\'t exists !',E_USER_ERROR);
                exit;
            }
            if(substr($p,-1) !='/')
                $p.='/';
            if($k!=0) 
                $allBasePath[]=$p;
            if ($handle = opendir($p)) {
                while (false !== ($f = readdir($handle))) {
                    if ($f{0} != '.' && is_dir($p.$f)) {
                        $result[$f]=$p.$f.'/';
                    }
                }
                closedir($handle);
            }
        }
        return $result;
    }


    /**
     * Analyse plugin paths
     * @param array|object $config the config container
     */
    static private function _loadPluginsPathList(&$config){
#if ENABLE_PHP_JELIX
        $list = split(' *, *',$config->pluginsPath);
#else
        $list = split(' *, *',$config['pluginsPath']);
#endif
        array_unshift($list, JELIX_LIB_PATH.'plugins/'); 
        foreach($list as $k=>$path){
            if(trim($path) == '') continue;
            $p = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
            if(!file_exists($p)){
                trigger_error('The path, '.$path.' given in the jelix config, doesn\'t exists !',E_USER_ERROR);
                exit;
            }
            if(substr($p,-1) !='/')
                $p.='/';

            if ($handle = opendir($p)) {
                while (false !== ($f = readdir($handle))) {
                    if ($f{0} != '.' && is_dir($p.$f)) {
                        if($subdir = opendir($p.$f)){
                            if($k!=0) 
#if ENABLE_PHP_JELIX
                               $config->_allBasePath[]=$p.$f.'/';
#else
                               $config['_allBasePath'][]=$p.$f.'/';
#endif
                            while (false !== ($subf = readdir($subdir))) {
                                if ($subf{0} != '.' && is_dir($p.$f.'/'.$subf)) {
                                    if($f == 'tpl'){
#if ENABLE_PHP_JELIX
                                        $prop = '_tplpluginsPathList_'.$subf;
                                        $config->{$prop}[] = $p.$f.'/'.$subf.'/';
                                    }else{
                                        $prop = '_pluginsPathList_'.$f;
                                        $config->{$prop}[$subf] = $p.$f.'/'.$subf.'/';
#else
                                        $config['_tplpluginsPathList_'.$subf][] = $p.$f.'/'.$subf.'/';
                                    }else{
                                        $config['_pluginsPathList_'.$f][$subf] =$p.$f.'/'.$subf.'/';
#endif
                                    }
                                }
                            }
                            closedir($subdir);
                        }
                    }
                }
                closedir($handle);
            }
        }
    }

    static private function _findServerName($ext) {
        $varname = '';
        $extlen = strlen($ext);

        if(strrpos($_SERVER['SCRIPT_NAME'], $ext) === (strlen($_SERVER['SCRIPT_NAME']) - $extlen) || php_sapi_name() == 'cli') {
            return 'SCRIPT_NAME';
        }else if (isset($_SERVER['REDIRECT_URL']) && strrpos( $_SERVER['REDIRECT_URL'], $ext) === (strlen( $_SERVER['REDIRECT_URL']) -$extlen)) {
            return 'REDIRECT_URL';
        }else if (isset($_SERVER['ORIG_SCRIPT_NAME']) && strrpos( $_SERVER['ORIG_SCRIPT_NAME'], $ext) === (strlen( $_SERVER['ORIG_SCRIPT_NAME']) - $extlen)) {
            return 'ORIG_SCRIPT_NAME';
        }
        throw new Exception('Jelix Error: in config file the parameter urlengine:scriptNameServerVariable is empty and Jelix don\'t find
            the variable in $_SERVER which contains the script name. You must see phpinfo and setup this parameter in your config file.');
    }

    /**
     * get all physical pathes of responses file
     */
    static private function _initResponsesPath(&$list){
        $copylist = $list; // because we modify $list and then it will search for "foo.path" responses...
        foreach($copylist as $type=>$class){
            if(file_exists($path=JELIX_LIB_CORE_PATH.'response/'.$class.'.class.php')){
                $list[$type.'.path']=$path;
            }elseif(file_exists($path=JELIX_APP_PATH.'responses/'.$class.'.class.php')){
                $list[$type.'.path']=$path;
            }else{
                throw new Exception('Jelix Config Error: the class file of the response type "'.$type.'" is not found ('.$path.')');
            }
        }
    }

#ifnot ENABLE_PHP_JELIX
    /**
     * merge two array which are the result of a parse_ini_file call
     * @param $array the main array
     * @param $tomerge the array to merge in the first one
     */
    static private function _mergeConfig(&$array, $tomerge){

        foreach($tomerge as $k=>$v){
            if(!isset($array[$k])){
                $array[$k] = $v;
                continue;
            }
            if($k{1} == '_')
                continue;
            if(is_array($v)){
                $array[$k] = array_merge($array[$k], $v);
            }else{
                $array[$k] = $v;
            }
        }

    }
#endif
}

?>
