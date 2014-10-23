<?php
/**
* @author   Laurent Jouanneau
* @contributor Bastien Jaillot
* @copyright 2007-2014 Laurent Jouanneau, 2008 Bastien Jaillot
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\SimpleLocalization {
// enclose namespace here because this file is inserted into jelix_check_server.php by a build tool

/**
 * Allow to access to some localized messages
 */
class Container {

    protected $currentLang;

    protected $messages = array();

    /**
     * @param string|array  list of path (or a single path) to files containing messages.
     *     if the path contains %LANG%, there should be a file for each lang. The content
     *     should be in an associative array key => translation. Else it contains translation
     *     for several lang, so the array should be array('lang code'=> array('key1'=>'message',...))
     */
    function __construct($langFilePath, $lang = '' ){
        if ($lang == '') {
            $lang = self::getLangFromRequest();
        }
        elseif (preg_match("/^([a-zA-Z]{2,3})(?:[-_]([a-zA-Z]{2,3}))?$/", $lang, $match)) {
            $lang = strtolower($match[1]);
        }

        if ($lang == '') {
            $lang = 'en';
        }
        if (is_array($langFilePath)) {
            foreach($langFilePath as $k=>$prefix) {
                if (is_array($prefix)) {
                    // this is an array of message, $k is lang
                    if (!isset($this->messages[$k])) {
                        $this->messages[$k] = array();
                    }
                    $this->messages[$k][] = $prefix;
                }
                else {
                    // this is a filename
                    $this->loadMessage($prefix, $lang);
                }
            }
        }
        else {
            $this->loadMessage($langFilePath, $lang);
        }
        $this->currentLang = $lang;
    }

    protected function loadMessage($filePath, $lang) {

        if (!isset($this->messages[$lang])) {
            $this->messages[$lang] = array();
        }

        if (strpos($filePath, '%LANG%') === false) {
            if (!file_exists($filePath)) {
                throw new \Exception("SimpleLocalization/Container: No file $filePath for messages for $lang");
            }
            $messages = include($filePath);
            if (!is_array($messages)) {
                throw new \Exception("SimpleLocalization/Container: Bad content in $filePath");
            }
            if (isset($messages[$lang]) && is_array($messages[$lang])) {
                $this->messages[$lang][] = $messages[$lang];
            }
            elseif (isset($messages['en']) && is_array($messages['en'])) {
                $this->messages[$lang][] = $messages['en'];
            }
            else {
                throw new \Exception("SimpleLocalization/Container: No '$lang' messages in $filePath");
            }
            return;
        }
        $file = str_replace('%LANG%', $lang, $filePath);
        if (!file_exists($file)) {
            $file = str_replace('%LANG%', 'en', $filePath);
            if (!file_exists($file)) {
                throw new \Exception("SimpleLocalization/Container: No file $file for messages for $lang");
            }
        }
        $messages = include($file);
        if (!is_array($messages)) {
            throw new \Exception("SimpleLocalization/Container: Bad content in $file");
        }
        $this->messages[$lang][] = $messages;
    }

    function get($key, $params = null){
        $msg = null;
        foreach($this->messages[$this->currentLang] as $messages) {
            if (isset($messages[$key])){
                $msg = $messages[$key];
            }
        }
        if ($msg === null) {
            throw new \Exception ("Error : don't find error message '$key'");
        }

        if ($params !== null || (is_array($params) && count($params) > 0)) {
            $msg = call_user_func_array('sprintf', array_merge (array ($msg), is_array ($params) ? $params : array ($params)));
        }
        return $msg;
    }

    function getLang(){
        return $this->currentLang;
    }

    /**
     * returns the locale corresponding of one of the accepted language indicated
     * by the browser
     * @return string the lang code. empty if not found.
     */
    static function getLangFromRequest() {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return '';

        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($languages as $bl) {
            if (preg_match("/^([a-zA-Z]{2,3})(?:[-_]([a-zA-Z]{2,3}))?(;q=[0-9]\\.[0-9])?$/", $bl, $match)) {
                $lang = strtolower($match[1]);
                return $lang;
            }
        }
        return '';
    }

}

} // end of namespace
