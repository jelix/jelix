<?php
/**
* @package  jelix
* @subpackage core
* @author   Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
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
        $config = jIniFile::read(JELIX_LIB_CORE_PATH.'defaultconfig.ini.php');

        if( $commonConfig = @parse_ini_file(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',true)){
            self::_mergeConfig($config, $commonConfig);
        }

        if($configFile !='defaultconfig.ini.php'){
            if(!file_exists(JELIX_APP_CONFIG_PATH.$configFile))
                die("Jelix config file $configFile is missing !");
            if( false === ($userConfig = @parse_ini_file(JELIX_APP_CONFIG_PATH.$configFile,true)))
                die("Syntax error in the Jelix config file $configFile !");
            self::_mergeConfig($config, $userConfig);
        }

        if(preg_match("/^(\w+).*$/", PHP_OS, $m)){
            $os=$m[1];
        }else{
            $os=PHP_OS;
        }
        $config['OS'] = $os;
        $os=strtolower($os);
        $config['isWindows'] = ((strpos($os,'win')!== false) && (strpos($os,'darwin')=== false));
        if(trim( $config['defaultAction']) == '')
             $config['defaultAction'] = '_';

        $config['_pluginsPathList'] = self::_loadPathList($config['pluginsPath']);
        $config['_modulesPathList'] = self::_loadPathList($config['modulesPath']);

        self::_loadTplPathList($config, 'tplpluginsPath');

        if($config['checkTrustedModules']){
            $config['_trustedModules'] = explode(',',$config['trustedModules']);
        }else{
            $config['_trustedModules'] = array_keys($config['_modulesPathList']);
        }
        $path=$config['urlengine']['basePath'];
        if($path!='/'){
            if($path{0} != '/') $path='/'.$path;
            if(substr($path,-1) != '/') $path.='/';
            $config['urlengine']['basePath'] = $path;
        }
        jIniFile::write($config, JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.resultini.php');
        $config = (object) $config;
        return $config;
    }

    /**
     * Analyse and check the "lib:" and "app:" path.
     * @param array $list list of "lib:*" and "app:*" path
     * @return array list of full path
     */
    static private function _loadPathList($list){
        $list = split(' *, *',$list);
        $result=array();
        foreach($list as $path){
            $p = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
            if(!file_exists($p)){
                trigger_error('The path, '.$path.' given in the jelix config, doesn\'t exists !',E_USER_ERROR);
                exit;
            }
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
     * Analyse and check the "lib:" and "app:" path for plugins
     * @param array $list list of "lib:*" and "app:*" path
     * @return array list of full path
     */
    static private function _loadTplPathList(&$config,  $var){
        $list = split(' *, *',$config[$var]);
        foreach($list as $path){
            $p = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
            if(!file_exists($p)){
                trigger_error('The path '.$path.' for tpl plugins, given in the jelix config, doesn\'t exists !',E_USER_ERROR);
                exit;
            }
            if ($handle = opendir($p)) {
                while (false !== ($f = readdir($handle))) {
                    if ($f{0} != '.' && is_dir($p.$f)) {
                        $config['_tplpluginsPathList_'.$f][] = $p.$f.'/';
                    }
                }
                closedir($handle);
            }
        }
    }

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
}

?>
