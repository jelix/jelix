<?php
/**
* @package    jelix
* @subpackage plugins
* @author   Jouanneau Laurent
* @copyright 2006 Laurent Jouanneau
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * plugin for language auto detection
 */
class AutoLocalePlugin implements jIPlugin {

    public $config;

    /**
    * @param    array  $config  list of configuration parameters
    */
    public function __construct($config){
        $this->config = $config;
    }

    /**
     * @param    jAction  $action  action that will be executed
     */
    public function beforeAction($action){

        global $gJCoord, $gJConfig;

        $langDetected=false;
        $lang='';

        $availableLang = explode(',',$this->config['availableLanguageCode']);

        if($this->config['enableUrlDetection']){
            $l = $gJCoord->request->getParam($this->config['urlParamNameLanguage']);
            if($l !==null && in_array($l, $availableLang)){
                $langDetected=true;
                $lang=$l;
            }
        }

        if(!$langDetected){
            if(isset($_SESSION['JX_LANG'])){
                $lang=$_SESSION['JX_LANG'];
            }else if($this->config['useDefaultLanguageBrowser']){
                $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                foreach($languages as $bl){
                    // pour les user-agents qui livrent un code internationnal
                    if(preg_match("/^([a-zA-Z]{2})(?:[-_]([a-zA-Z]{2}))?(;q=[0-9]\\.[0-9])?$/",$bl,$match)){
                        if(isset($match[2]))
                            $l=$match[1].'_'.strtoupper($match[2]);
                        else
                            $l=$match[1].'_'.strtoupper($match[1]);
                        if(in_array($l, $availableLang)){
                            $lang= $l;
                            break;
                        }

                    // pour les user agent qui indique le nom en entier
                    }elseif(preg_match("/^([a-zA-Z ]+)(;q=[0-9]\\.[0-9])?$/",$bl,$match)){
                        $langs = array('french'=>'fr_FR', 'english'=>'en_US');
                        if(isset($langs[$match[1]])){
                            $lang= $langs[$match[1]];
                            break;
                        }
                    }
                }
            }
        }

        if($lang!=''){
            $_SESSION['JX_LANG']=$lang;
            $gJConfig->defaultLocale = $lang;
        }
    }

    /**
     *
     */
    public function beforeOutput() {}

    public function afterProcess() {}

}
?>
