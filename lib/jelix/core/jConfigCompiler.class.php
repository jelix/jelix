<?php
/**
* @package  jelix
* @subpackage core
* @version  $Id$
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
        $config = parse_ini_file(JELIX_LIB_CORE_PATH.'defaultconfig.ini.php', true);

        if( $commonConfig = @parse_ini_file(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',true)){
            self::_mergeConfig($config, $commonConfig);
        }

        if($configFile !='defaultconfig.ini.php'){
            if(!file_exists(JELIX_APP_CONFIG_PATH.$configFile))
                die(" fichier de configuration manquant !");
            if( false === ($userConfig = @parse_ini_file(JELIX_APP_CONFIG_PATH.$configFile,true)))
                die(" Erreur dans le fichier de configuration !");
            self::_mergeConfig($config, $userConfig);
        }

        if(preg_match("/^(\w+).*$/", PHP_OS, $m)){
            $os=$m[1];
        }else{
            $os=PHP_OS;
        }
        $config['OS'] = $os;
        $config['isWindows'] = (strpos(strtolower($os),'win')!== false);
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
        self::_saveToIni($config, JELIX_APP_TEMP_PATH.$configFile.'.resultini.php');
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
            $path = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
            if(!file_exists($path)){
                trigger_error($path.' path given in the config doesn\'t exist',E_USER_ERROR);
                exit;
            }
            if ($handle = opendir($path)) {
                while (false !== ($f = readdir($handle))) {
                    if ($f{0} != '.' && is_dir($path.$f)) {
                        $result[$f]=$path.$f.'/';
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
            $path = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
            if(!file_exists($path)){
                trigger_error($path.' path given in the config doesn\'t exist',E_USER_ERROR);
                exit;
            }
            if ($handle = opendir($path)) {
                while (false !== ($f = readdir($handle))) {
                    if ($f{0} != '.' && is_dir($path.$f)) {
                        $config['_tplpluginsPathList_'.$f][] = $path.$f.'/';
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

    /**
     * store an ini array in a file (contrary of parse_ini_file)
     * @param $array the array to store
     * @param $filename the file name
     */
    static private function _saveToIni($array,$filename){

        $result='';
        foreach($array as $k=>$v){
            if(is_array($v)){
                $result.='['.$k."]\n";
                foreach($v as $k2=>$v2){
                    $result .= $k2.'='.self::_iniValue($v2)."\n";
                }
            }else{
                // on met les valeurs simples en debut de fichier
                $result = $k.'='.self::_iniValue($v)."\n".$result;
            }
        }

        if($f = @fopen($filename, 'wb')){
            fwrite($f, $result);
            fclose($f);
        }else{
            trigger_error('Can\'t write '.$filename.' file',E_USER_ERROR);
        }
    }

    /**
     * format a value to store in a ini file
     * @param string $value the value
     * @return string the formated value
     */
    static private function _iniValue($value){
        if($value=='' || is_numeric($value) || preg_match("/^[\w]*$/", $value))
            return $value;
        else
            return '"'.$value.'"';
    }
}

?>