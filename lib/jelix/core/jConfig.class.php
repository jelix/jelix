<?php
/**
* @package  jelix
* @subpackage core
* @version  $Id$
* @author   Jouanneau Laurent
* @contributor
* @copyright 2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jConfig {


    private function __construct (){ }

    /**
     * lecture de la configuration du framework
     */
    static public function load($configFile){
        global $gDefaultConfig;

        $config = parse_ini_file(JELIX_APP_CONFIG_PATH.$configFile,true);

        // traitement spécial pour la liste des réponses.
        if(isset($config['responses'])){
           $resplist = array_merge($gDefaultConfig['responses'],$config['responses']) ;
        }else{
           $resplist = $gDefaultConfig['responses'];
        }

        $config = array_merge($gDefaultConfig,$config);
        $config['responses'] = $resplist;
        $config = (object) $config;

        if(preg_match("/^(\w+).*$/", PHP_OS, $m)){
            $os=$m[1];
        }else{
            $os=PHP_OS;
        }
        $config->OS = $os;
        $config->isWindows = (strtolower($os) == 'win');
        if(trim( $config->defaultAction) == '')
             $config->defaultAction = '_';

        $config->pluginsPathList = self::_loadPathList($config, $configFile,'plugins');
        $config->modulesPathList = self::_loadPathList($config, $configFile, 'modules');
        $config->tplpluginsPathList = self::_loadPathList($config, $configFile, 'tplplugins',true);

        if($config->checkTrustedModules){
            $config->trustedModules = explode(',',$config->trustedModules);
        }else{
            $config->trustedModules = array_keys($config->modulesPathList);
        }

        return $config;
    }

    /**
     * compilation et mise en cache de liste de chemins
     */
    static private function _loadPathList($config, $configFile, $dir, $tplp=false){

        $file = JELIX_APP_TEMP_PATH.$configFile.'.'.$dir.'list.ini.php';
        $compil=false;
        if(!file_exists($file)){
            $compil=true;
        }else if($config->compilation['checkCacheFiletime']){
            $t = filemtime($file);
            if(filemtime(JELIX_APP_CONFIG_PATH.$configFile)>$t){
                $compil=true;
            }else{

                $list = split(' *, *',$config->{$dir.'Path'});
                foreach($list as $p){
                    $path = str_replace(array('lib:','app:'), array(LIB_PATH,JELIX_APP_PATH), $p);
                    if(!file_exists($path)){
                        trigger_error($p.' path doesn\'t exist',E_USER_ERROR);
                        exit;
                    }
                    if(filemtime($path)>$t){
                        $compil=true;
                        break;
                    }
                }
            }
        }
        if($compil){
            $list = split(' *, *',$config->{$dir.'Path'});
            $result='';
            foreach($list as $path){
                $path = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
                if ($handle = opendir($path)) {
                     while (false !== ($f = readdir($handle))) {
                        if ($f{0} != '.' && is_dir($path.$f)) {
                           if($tplp){
                              $result[$f][] = $path.$f.'/';
                           }else{
                              $result.=$f.'='.$path.$f."/\n";
                           }
                        }
                     }
                    closedir($handle);
                }
            }
            if($f = @fopen($file, 'wb')){
                if($tplp){
                   $result = serialize($result);
                }
                fwrite($f, $result);
                fclose($f);
            }else{
                trigger_error('Can\'t write '.$file.' file',E_USER_ERROR);
            }
        }
        if($tplp){
           return unserialize(file_get_contents($file));
        }else{
           return parse_ini_file($file);
        }
    }

}


?>