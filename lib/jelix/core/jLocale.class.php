<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @author     Gerald Croes
* @contributor Julien Issler
* @copyright  2001-2005 CopixTeam, 2005-2007 Laurent Jouanneau
* Some parts of this file are took from Copix Framework v2.3dev20050901, CopixI18N.class.php, http://www.copix.org.
* copyrighted by CopixTeam and released under GNU Lesser General Public Licence.
* initial authors : Gerald Croes, Laurent Jouanneau.
* enhancement by Laurent Jouanneau for Jelix.
* @copyright 2008 Julien Issler
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
* a bundle contains all readed properties in a given language, and for all charsets
* @package  jelix
* @subpackage core
*/
class jBundle {
    public $fic;
    public $locale;

    protected $_loadedCharset = array ();
    protected $_strings = array();

    /**
    * constructor
    * @param jSelector   $file selector of a properties file
    * @param string      $locale    the code lang
    */
    public function __construct ($file, $locale){
        $this->fic  = $file;
        $this->locale = $locale;
    }

    /**
    * get the translation
    * @param string $key the locale key
    * @param string $charset
    * @return string the localized string
    */
    public function get ($key, $charset = null){

        if($charset == null){
            $charset = $GLOBALS['gJConfig']->charset;
        }
        if (!in_array ($charset, $this->_loadedCharset)){
            $this->_loadLocales ($this->locale, $charset);
        }

        if (isset ($this->_strings[$charset][$key])){
            return $this->_strings[$charset][$key];
        }else{
            return null;
        }
    }

    /**
    * Loads the resources for a given locale/charset.
    * @param string $locale     the locale
    * @param string $charset    the charset
    */
    protected function _loadLocales ($locale, $charset){
        global $gJConfig;
        $this->_loadedCharset[] = $charset;

        $source = $this->fic->getPath();
        $cache = $this->fic->getCompiledFilePath();

        // check if we have a compiled version of the ressources

        if (is_readable ($cache)){
            $okcompile = true;

            if ($gJConfig->compilation['force']){
               $okcompile = false;
            }else{
                if ($gJConfig->compilation['checkCacheFiletime']){
                    if (is_readable ($source) && filemtime($source) > filemtime($cache)){
                        $okcompile = false;
                    }
                }
            }

            if ($okcompile) {
                include ($cache);
                $this->_strings[$charset] = $_loaded;
                return;
            }
        }

        $this->_loadResources ($source, $charset);

        if(isset($this->_strings[$charset])){
            $content = '<?php $_loaded= '.var_export($this->_strings[$charset], true).' ?>';

            jFile::write($cache, $content);
        }
    }


    /**
    * loads a given resource from its path.
    */
    protected function _loadResources ($fichier, $charset){

        if (($f = @fopen ($fichier, 'r')) !== false) {
            $multiline=false;
            $linenumber=0;
            $key='';
            while (!feof($f)) {
                if($line=fgets($f)){
                    $linenumber++;
                    $line=rtrim($line);
                    if($multiline){
                        if(preg_match("/^\s*(.*)\s*(\\\\?)$/U", $line, $match)){
                            $sp = preg_split('/(?<!\\\\)\#/', $match[1], -1 ,PREG_SPLIT_NO_EMPTY);
                            $multiline= ($match[2] =="\\");
                            $this->_strings[$charset][$key].=' '.trim(str_replace(array('\#','\n'),array('#',"\n"),$sp[0]));
                        }else{
                            throw new Exception('Syntaxe error in file properties '.$fichier.' line '.$linenumber,210);
                        }
                    }elseif(preg_match("/^\s*(.+)\s*=\s*(.*)\s*(\\\\?)$/U",$line, $match)){
                        // on a bien un cle=valeur
                        $key=$match[1];
                        $multiline= ($match[3] =="\\");
                        $sp = preg_split('/(?<!\\\\)\#/', $match[2], -1 ,PREG_SPLIT_NO_EMPTY);
                        if(count($sp)){
                            $value=trim(str_replace('\#','#',$sp[0]));
                            if($value == '\w'){
                                $value = ' ';
                            }
                        }else{
                            $value='';
                        }

                        $this->_strings[$charset][$key] = str_replace(array('\#','\n'),array('#',"\n"),$value);

                    }elseif(preg_match("/^\s*(\#.*)?$/",$line, $match)){
                        // ok, juste un commentaire
                    }else {
                        throw new Exception('Syntaxe error in file properties '.$fichier.' line '.$linenumber,211);
                    }
                }
            }
            fclose ($f);
        }else{
            throw new Exception('Cannot load the resource '.$fichier,212);
        }
    }
}


/**
 * static class to get a localized string
 * @package  jelix
 * @subpackage core
 */
class jLocale {
    /**
     *
     */
    static $bundles = array();

    /**
     * static class...
     */
    private function __construct(){}

    /**
     * gets the current lang
     * @return string
     */
    static function getCurrentLang(){
        $s=$GLOBALS['gJConfig']->locale;
        return substr($s,0, strpos($s,'_'));
    }
    /**
     * gets the current country.
     * @return string
     */
    static function getCurrentCountry (){
        $s=$GLOBALS['gJConfig']->locale;
        return substr($s,strpos($s,'_')+1);
    }

    /**
    * gets the correct string, for a given language.
    *   if it can't get the correct language, it will try to gets the string
    *   from the default language.
    *   if both fails, it will raise an exception.
    * @param string $key the key of the localized string
    * @param array $args arguments to apply to the localized string with sprintf
    * @param string $locale  the lang code. if null, use the default language
    * @param string $charset the charset code. if null, use the default charset
    * @return string the localized string
    */
    static function get ($key, $args=null, $locale=null, $charset=null) {
        global $gJConfig;
        try{
            $file = new jSelectorLoc($key, $locale, $charset);
        }catch(jExceptionSelector $e){
            if($e->getCode() == 12) throw $e;
            if ($locale === null)  $locale = $gJConfig->locale;
            if ($charset === null) $charset = $gJConfig->charset;
            throw new Exception('(200)The given locale key "'.$key.'" is invalid (for charset '.$charset.', lang '.$locale.')');
        }

        $locale = $file->locale;
        $keySelector = $file->module.'~'.$file->fileKey;
        if (!isset (self::$bundles[$keySelector][$locale])){
            self::$bundles[$keySelector][$locale] =  new jBundle ($file, $locale);
        }
        $bundle = self::$bundles[$keySelector][$locale];

        //try to get the message from the bundle.
        $string = $bundle->get ($file->messageKey, $file->charset);
        if ($string === null){
            //if the message was not found, we're gonna
            //use the default language and country.
            if ($locale == $gJConfig->locale){
                throw new Exception('(210)The given locale key "'.$file->toString().'" does not exists in the default lang for the '.$file->charset.' charset');
            }
            return jLocale::get ($key, $args, $gJConfig->locale);
        }else{
            //here, we know the message
            if ($args!==null){
                $string = call_user_func_array('sprintf', array_merge (array ($string), is_array ($args) ? $args : array ($args)));
            }
            return $string;
        }
    }
}
?>
