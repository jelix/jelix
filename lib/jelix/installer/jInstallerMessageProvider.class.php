<?php
/**
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
 * message provider for jInstallCheck and jInstaller
 * @package  jelix
 * @subpackage core
 * @since 1.0b2
 */
class jInstallerMessageProvider {
    protected $currentLang;

    protected $messages = array(
        'fr'=>array(
#include messageProvider.fr.inc.php
        ),

        'en'=>array(
#include messageProvider.en.inc.php
        ),
    );

    function __construct($lang=''){
        if($lang == '' && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach($languages as $bl){
                // pour les user-agents qui livrent un code internationnal
                if(preg_match("/^([a-zA-Z]{2,3})(?:[-_]([a-zA-Z]{2,3}))?(;q=[0-9]\\.[0-9])?$/",$bl,$match)){
                    $lang = strtolower($match[1]);
                    break;
                }
            }
        }elseif(preg_match("/^([a-zA-Z]{2,3})(?:[-_]([a-zA-Z]{2,3}))?$/",$lang,$match)){
            $lang = strtolower($match[1]);
        }
        if($lang == '' || !isset($this->messages[$lang])){
            $lang = 'en';
        }
        $this->currentLang = $lang;
    }

    function get($key, $params = null){
        if(isset($this->messages[$this->currentLang][$key])){
            $msg = $this->messages[$this->currentLang][$key];
        }else{
            throw new Exception ("Error : don't find error message '$key'");
        }

        if ($params !== null || (is_array($params) && count($params) > 0)) {
            $msg = call_user_func_array('sprintf', array_merge (array ($msg), is_array ($params) ? $params : array ($params)));
        }
        return $msg;
    }

    function getLang(){
        return $this->currentLang;
    }
}
