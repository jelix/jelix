<?php

/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*
* Some parts of this file are took from Copix Framework v2.3dev20050901, CopixI18N.class.php,
* copyrighted by CopixTeam and released under GNU Lesser General Public Licence
* author : Gerald Croes, Laurent Jouanneau
* http://www.copix.org
*/


/*
* Contient un ensemble de chaines concernant une locale donnée
* et pour tous les charsets
*/
class jBundle {
    var $fic;
    var $locale;

    private $_loadedCharset = array ();
    private $_strings = array();

    /**
    * constructor
    * @param jSelector   $file
    * @param string      $locale
    */
    function __construct ($file, $locale){
        $this->fic  = $file;
        $this->locale = $locale;
    }

    /**
    * get the translation
    */
    public function get ($key, $charset = null){

        if($charset == null){
            $charset = $GLOBALS['gJConfig']->defaultCharset;
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
    private function _loadLocales ($locale, $charset){
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

            $file = new jFile();
            $file->write($cache, $content);
        }
    }


    /**
    * loads a given resource from its path.
    */
    function _loadResources ($fichier, $charset){

        if (($f = fopen ($fichier, 'r')) !== false) {
            $multiline=false;
            $linenumber=0;
            while (!feof($f)) {
                if($line=fgets($f)){
                    $linenumber++;

                    if($multiline){
                        if(preg_match("/^([^#]+)(\#?.*)$/", $line, $match)){ // toujours vrai en fait
                            $value=trim($match[1]);
                            if($multiline= (substr($value,-1) =="\\"))
                                $this->_strings[$charset][$key].=substr($value,0,-1);
                            else
                                $this->_strings[$charset][$key].=$value;
                        }
                    }elseif(preg_match("/^\s*(([^#=]+)=([^#]+))?(\#?.*)$/",$line, $match)){
                        if($match[1] != ''){
                            // on a bien un cle=valeur
                            $value=trim($match[3]);
                            if($multiline= (substr($value,-1) =="\\")){
                                $value=substr($value,0,-1);
                            }

                            $key=trim($match[2]);

                            if($value == '\w'){
                                $value = ' ';
                            }

                            $this->_strings[$charset][$key] =$value;
                        }else{
                            if($match[4] != '' && substr($match[4],0,1) != '#'){
                                trigger_error('Syntaxe error in file properties '.$fichier.' line '.$linenumber, E_USER_NOTICE);
                            }
                        }
                    }else {
                        trigger_error('Syntaxe error in file properties '.$fichier.' line '.$linenumber, E_USER_NOTICE);
                    }
                }
            }
            fclose ($f);
        }else{
            trigger_error ('Cannot load the resource '.$fichier, E_USER_ERROR);
        }
    }
}



class jLocale {
    static $bundles = array();

    /**
    * gets the current lang
    */
    function getCurrentLang(){
        $s=$GLOBALS['gJConfig']->defaultLocale;
        return substr($s,0, strpos($s,'_'));
    }
    /**
    * gets the current country.
    */
    function getCurrentCountry (){
        $s=$GLOBALS['gJConfig']->defaultLocale;
        return substr($s,strpos($s,'_')+1);
    }

    /**
    * gets the correct string, for a given language.
    *   if it can't get the correct language, it will try to gets the string
    *   from the default language.
    *   if both fails, it will raise a fatal_error.
    */
    static function get ($key, $args=null, $locale=null, $charset=null) {
        global $gJConfig;
        if ($locale === null){
            $locale = $gJConfig->defaultLocale;
        }
        if ($charset === null){
            $charset = $gJConfig->defaultCharset;
        }
        if(strpos($locale,'_') === false){
            $locale.='_'.strtoupper($locale);
        }
        //Gets the bundle for the given language.
        $pos = strpos ($key, '.');
        $keySelector = substr ($key, 0, $pos);
        $messageKey = substr($key, $pos+1);

        $file = new jSelectorLoc($keySelector, $locale, $charset);

        if (!$file->isValid()){
            if($key == 'jelix~errors.locale.key.selector.invalid'){
                return '(200)The given locale key "'.$args[0].'" is invalid  (for module '.$args[1].', charset '.$args[2].', lang '.$args[3].') (internal error ?)';
            }else{
                trigger_error (jLocale::get ('jelix~errors.locale.key.selector.invalid', array($key,$file->module, $charset, $locale)), E_USER_ERROR);
            }
        }

        if (!isset (self::$bundles[$keySelector][$locale])){
            self::$bundles[$keySelector][$locale] =  new jBundle ($file, $locale);
        }
        $bundle = self::$bundles[$keySelector][$locale];

        //try to get the message from the bundle.
        $string = $bundle->get ($messageKey, $charset);
        if ($string === null){
            //if the message was not found, we're gonna
            //use the default language and country.
            if ($locale    == $gJConfig->defaultLocale){
                if ($key == 'jelix~errors.locale.key.unknow'){
                    $msg = 'Can\'t find message key (which should actually be THIS message): '.$key;
                }else{
                    $msg = jLocale::get ('jelix~errors.locale.key.unknow',array($key,$file->module, $charset, $locale));
                }
                trigger_error ($msg, E_USER_ERROR);
                return null;
            }
            return jLocale::get ($key, $args, $gJConfig->defaultLocale);
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
