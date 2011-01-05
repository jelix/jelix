<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Laurent Jouanneau
* @contributor Yann, Dominique Papin
* @contributor Warren Seine, Alexis Métaireau, Julien Issler, Olivier Demah, Brice Tence
* @copyright   2005-2009 Laurent Jouanneau, 2006 Yann, 2007 Dominique Papin
* @copyright   2008 Warren Seine, Alexis Métaireau
* @copyright   2009 Julien Issler, Olivier Demah
* @copyright   2010 Brice Tence
*              few lines of code are copyrighted CopixTeam http://www.copix.org
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
*
*/
require_once(JELIX_LIB_PATH.'tpl/jTpl.class.php');
require_once(JELIX_LIB_PATH.'utils/jMinifier.class.php');

/**
* HTML response
* @package  jelix
* @subpackage core_response
*/
class jResponseHtml extends jResponse {
    /**
    * jresponse id
    * @var string
    */
    protected $_type = 'html';

    /**
     * Title of the document
     * @var string
     */
    public $title = '';

    /**
     * favicon url linked to the document
     * @var string
     * @since 1.0b2
     */
    public $favicon = '';

    /**
     * The template engine used to generate the body content
     * @var jTpl
     */
    public $body = null;

    /**
     * selector of the main template file
     * This template should contains the body content, and is used by the $body template engine
     * @var string
     */
    public $bodyTpl = '';

    /**
     * Selector of the template used when there are some errors, instead of $bodyTpl
     * @var string
     */
    public $bodyErrorTpl = '';

    /**
     * body attributes
     * This attributes are written on the body tags
     * @var array
     */
    public $bodyTagAttributes= array();

    /**
     * says what part of the html head has been send
     * @var integer
     */
    protected $_headSent = 0;

    /**
     * the charset of the document
     * @var string
     */
    protected $_charset;

    /**
     * the lang of the document
     * @var string
     */
    protected $_lang;

    /**
     * properties of the head content
     */

    /**#@+
     * content for the head
     * @var array
     */
    protected $_CSSLink = array ();
    protected $_CSSIELink = array ();
    protected $_Styles  = array ();
    protected $_JSLink  = array ();
    protected $_JSIELink  = array ();
    protected $_JSCodeBefore  = array ();
    protected $_JSCode  = array ();
    protected $_Others  = array ();
    protected $_MetaKeywords = array();
    protected $_MetaDescription = array();
    protected $_MetaAuthor = '';
    protected $_MetaGenerator = '';
    protected $_Link = array();
    /**#@-*/

    /**#@+
     * content for the body
     * @var array
     */
    protected $_bodyTop = array();
    protected $_bodyBottom = array();
    /**#@-*/

    /**
     * says if the document is in xhtml or html
     */
    protected $_isXhtml = true;
    protected $_endTag="/>\n";

    /**
     * says if the document uses a Strict or Transitional Doctype
     * @var boolean
     * @since 1.1.3
     */
    protected $_strictDoctype = true;

    /**
     * says if xhtml content type should be send or not.
     * it true, a verification of HTTP_ACCEPT is done.
     * @var boolean
     */
    public $xhtmlContentType = false;


    /**
    * constructor;
    * setup the charset, the lang, the template engine
    */
    function __construct (){
        global $gJConfig;
        $this->_charset = $gJConfig->charset;
        $this->_lang = $gJConfig->locale;
        $this->body = new jTpl();
        parent::__construct();
    }

    /**
     * output the html content
     *
     * @return boolean    true if the generated content is ok
     */
    final public function output(){
        $this->doAfterActions();

        $this->_headSent = 0;
        if($this->_isXhtml && $this->xhtmlContentType && strstr($_SERVER['HTTP_ACCEPT'],'application/xhtml+xml')){
            $this->_httpHeaders['Content-Type']='application/xhtml+xml;charset='.$this->_charset;
        }else{
            $this->_httpHeaders['Content-Type']='text/html;charset='.$this->_charset;
        }
        $this->sendHttpHeaders();
        $this->outputDoctype();
        $this->_headSent = 1;
        
        if($this->bodyTpl != '')
            $this->body->meta($this->bodyTpl);
        $this->outputHtmlHeader();
        echo '<body ';
        foreach($this->bodyTagAttributes as $attr=>$value){
            echo $attr,'="', htmlspecialchars($value),'" ';
        }
        echo ">\n";
        $this->_headSent = 2;
        echo implode("\n",$this->_bodyTop);
        if($this->bodyTpl != '')
            $this->body->display($this->bodyTpl);

        if($this->hasErrors()){
            if($GLOBALS['gJConfig']->error_handling['showInFirebug']){
                echo '<script type="text/javascript">if(console){';
                foreach( $GLOBALS['gJCoord']->errorMessages  as $e){
                    switch ($e[0]) {
                      case 'warning':
                        echo 'console.warn("[warning ';
                        break;
                      case 'notice':
                        echo 'console.info("[notice ';
                        break;
                      case 'strict':
                        echo 'console.info("[strict ';
                        break;
                      case 'error':
                        echo 'console.error("[error ';
                        break;
                    }
                    $m = $e[2]. ($e[5]?"\n".$e[5]:"");
                    echo $e[1],'] ',str_replace(array('"',"\n","\r","\t"),array('\"','\\n','\\r','\\t'),$m),' (',str_replace('\\','\\\\',$e[3]),' ',$e[4],')");';
                }
                echo '}else{alert("there are some errors, you should activate Firebug to see them");}</script>';
            }else{
                echo '<div id="jelixerror" style="position:absolute;left:0px;top:0px;border:3px solid red; background-color:#f39999;color:black;z-index:100;">';
                echo $this->getFormatedErrorMsg();
                echo '<p><a href="#" onclick="document.getElementById(\'jelixerror\').style.display=\'none\';return false;">close</a></p></div>';
            }
        }
        echo implode("\n",$this->_bodyBottom);
        if(count($GLOBALS['gJCoord']->logMessages)) {
            if(count($GLOBALS['gJCoord']->logMessages['response'])) {
                echo '<ul id="jelixlog">';
                foreach($GLOBALS['gJCoord']->logMessages['response'] as $m) {
                    echo '<li>',htmlspecialchars($m),'</li>';
                }
                echo '</ul>';
            }
            if(count($GLOBALS['gJCoord']->logMessages['firebug'])) {
                echo '<script type="text/javascript">if(console){';
                foreach($GLOBALS['gJCoord']->logMessages['firebug'] as $m) {
                    echo 'console.debug("',str_replace(array('"',"\n","\r","\t"),array('\"','\\n','\\r','\\t'),$m),'");';
                }
                echo '}else{alert("there are log messages, you should activate Firebug to see them");}</script>';
            }
        }
        echo '</body></html>';
        return true;
    }

    /**
     * The method you can overload in your inherited html response
     * overload it if you want to add processes (stylesheet, head settings, additionnal content etc..)
     * after all actions
     * @since 1.1
     */
    protected function doAfterActions(){

    }

    /**
     * output errors
     */
    final public function outputErrors(){
        if($this->_headSent < 1){
             if(!$this->_httpHeadersSent){
                header("HTTP/1.0 500 Internal Server Error");
                header('Content-Type: text/html;charset='.$this->_charset);
             }
            echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">', "\n<html>";
        }
        if($this->_headSent < 2){
            echo '<head><title>Errors</title></head><body>';
        }
        if($this->hasErrors()){
            echo $this->getFormatedErrorMsg();
        }else{
            echo '<p style="color:#FF0000">Unknown Error</p>';
        }
        echo '</body></html>';
    }


    /**
     * create html error messages
     * @return string html content
     */
    protected function getFormatedErrorMsg(){
        $errors='';
        foreach( $GLOBALS['gJCoord']->errorMessages  as $e){
           $errors .=  '<p style="margin:0;"><b>['.$e[0].' '.$e[1].']</b> <span style="color:#FF0000">';
           $errors .= htmlspecialchars($e[2], ENT_NOQUOTES, $this->_charset)."</span> \t".$e[3]." \t".$e[4]."</p>\n";
           if ($e[5])
            $errors.= '<pre>'.htmlspecialchars($e[5], ENT_NOQUOTES, $this->_charset).'</pre>';
        }
        return $errors;
    }

    /**
     * add content to the body
     * you can add additionnal content, before or after the content generated by the main template
     * @param string $content additionnal html content
     * @param boolean $beforeTpl true if you want to add it before the template content, else false for after
     */
    function addContent($content, $beforeTpl = false){
      if($beforeTpl){
        $this->_bodyTop[]=$content;
      }else{
         $this->_bodyBottom[]=$content;
      }
    }
    
    /**
     * add a generic link to the head
     * 
     * @param string $href  url of the link
     * @param string $rel   relation name
     * @param string $type  mime type of the ressource
     * @param string $title
     */ 
    final public function addLink($href, $rel, $type='', $title='') {
        $this->_Link[$href] = array($rel, $type, $title);
    }

    /**
     * add a link to a javascript script in the document head
     *
     * $forIe parameter exists since 1.0b2
     *
     * @param string $src the link
     * @param array $params additionnals attributes for the script tag
     * @param boolean $forIE if true, the script sheet will be only for IE browser
     */
    final public function addJSLink ($src, $params=array(), $forIE=false){
        if($forIE){
            if (!isset ($this->_JSIELink[$src])){
                $this->_JSIELink[$src] = $params;
            }
        }else{
            if (!isset ($this->_JSLink[$src])){
                $this->_JSLink[$src] = $params;
            }
        }
    }

    /**
     * add a link to a css stylesheet in the document head
     *
     * $forIe parameter exists since 1.0b2
     *
     * @param string $src the link
     * @param array $params additionnals attributes for the link tag
     * @param mixed $forIE if true, the style sheet will be only for IE browser. string values possible (ex:'lt IE 7')
     */
    final public function addCSSLink ($src, $params=array (), $forIE=false){
        if($forIE){
            if (!isset ($this->_CSSIELink[$src])){
                if (!is_bool($forIE) && !empty($forIE))
                    $params['_ieCondition'] = $forIE;
                $this->_CSSIELink[$src] = $params;
            }
        }else{
            if (!isset ($this->_CSSLink[$src])){
                $this->_CSSLink[$src] = $params;
            }
        }
    }

    /**
     * add inline css style into the document (inside a <style> tag)
     * @param string $selector css selector
     * @param string $def      css properties for the given selector
     */
    final public function addStyle ($selector, $def=null){
        if (!isset ($this->_Styles[$selector])){
            $this->_Styles[$selector] = $def;
        }
    }

    /**
     * add additional content into the document head
     * @param string $content
     * @since 1.0b1
     */
    final public function addHeadContent ($content){
        $this->_Others[] = $content;
    }

    /**
     * add inline javascript code (inside a <script> tag)
     * @param string $code  javascript source code
     * @param boolean $before will insert the code before js links if true
     */
    final public function addJSCode ($code, $before = false){
        if ($before)
            $this->_JSCodeBefore[] = $code;
        else
            $this->_JSCode[] = $code;
    }

    /**
     * add some keywords in a keywords meta tag
     * @author Yann
     * @param string $content keywords
     * @since 1.0b1
     */
    final public function addMetaKeywords ($content){
        $this->_MetaKeywords[] = $content;
    }
    /**
     * add a description in a description meta tag
     * @author Yann
     * @param string $content a description
     * @since 1.0b1
     */
    final public function addMetaDescription ($content){
        $this->_MetaDescription[] = $content;
    }
    /**
     * add author(s) in a author meta tag
     * @author Olivier Demah
     * @param string $content author(s)
     * @since 1.2
     */
    final public function addMetaAuthor($content){
        $this->_MetaAuthor = $content;
    }
    /**
     * add generator a generator meta tag
     * @author Olivier Demah
     * @param string $content generator
     * @since 1.2
     */
    final public function addMetaGenerator($content){
        $this->_MetaGenerator = $content;
    }
    /**
     * generate the doctype. You can override it if you want to have your own doctype, like XHTML+MATHML.
     * @since 1.1
     */
    protected function outputDoctype (){
        if($this->_isXhtml){
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 '.($this->_strictDoctype?'Strict':'Transitional').'//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-'.($this->_strictDoctype?'strict':'transitional').'.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="',$this->_lang,'" lang="',$this->_lang,'">
';
        }else{
            echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01'.($this->_strictDoctype?'':' Transitional').'//EN" "http://www.w3.org/TR/html4/'.($this->_strictDoctype?'strict':'loose').'.dtd">', "\n";
            echo '<html lang="',$this->_lang,'">';
        }
    }

    final protected function outputJsScriptTag( $fileUrl, $scriptParams, $filePath = null ) {
        global $gJConfig;

        $params = '';
        if( is_array($scriptParams) ) {
            foreach ($scriptParams as $param_name=>$param_value){
                $params .= $param_name.'="'. htmlspecialchars($param_value).'" ';
            }
        } else {
            $params = $scriptParams;
        }

        $jsFilemtime = '';
        if( isset($gJConfig->jResponseHtml) && $gJConfig->jResponseHtml['jsUniqueUrlId']
            && $filePath !== null
            && (strpos($fileUrl,'http://')===FALSE) //path is not absolute
          ) {
            $jsFilemtime = "?".filemtime($filePath);
        }
        echo '<script type="text/javascript" src="',htmlspecialchars($fileUrl),$jsFilemtime,'" ',$params,'></script>',"\n";
    }



    final protected function outputCssLinkTag( $fileUrl, $cssParams, $filePath = null ) {
        global $gJConfig;

        $params = '';
        if( is_array($cssParams) ) {
            foreach ($cssParams as $param_name=>$param_value){
                $params .= $param_name.'="'. htmlspecialchars($param_value).'" ';
            }
        } else {
            $params = $cssParams;
        }

        $cssFilemtime = '';
        if( isset($gJConfig->jResponseHtml) && $gJConfig->jResponseHtml['cssUniqueUrlId']
            && $filePath !== null
            && (strpos($fileUrl,'http://')===FALSE) //path is not absolute
          ) {
            $cssFilemtime = "?".filemtime($filePath);
        }
        echo '<link type="text/css" href="',htmlspecialchars($fileUrl),$cssFilemtime,'" ',$params,$this->_endTag,"\n";
    }




    final protected function outputJsScripts( &$scriptList ) {
        global $gJConfig;

        $minifyJsByParams = array();
        $minifyExcludeJS = array();

        if( isset($gJConfig->jResponseHtml) && $gJConfig->jResponseHtml['minifyExcludeJS'] ) {
            $minifyExcludeJS = explode( ',', $gJConfig->jResponseHtml['minifyExcludeJS'] );
        }

        $basePath = $gJConfig->urlengine['basePath'];

        foreach ($scriptList as $src=>$params){
            //the extra params we may found in there.
            $scriptParams = '';

            $pathSrc = $src;
            if ( $basePath != '/' && $basePath != '' ) {
                    $res = explode($basePath, $src);
                    if ( count($res) > 1 )
                        list(,$pathSrc) = $res;
                }

            $pathIsAbsolute = (strpos($pathSrc,'http://')!==FALSE);

            if( isset($gJConfig->jResponseHtml) && $gJConfig->jResponseHtml['minifyJS'] &&
                ! $pathIsAbsolute && ! in_array(basename($pathSrc), $minifyExcludeJS) ) {
                //this file should be minified
                $sparams=$params;
                ksort($sparams); //sort to avoid duplicity just because of params order
                foreach ($sparams as $param_name=>$param_value){
                    $scriptParams .= $param_name.'="'. htmlspecialchars($param_value).'" ';
                }
                $minifyJsByParams[$scriptParams][] = "$src";
            } else {
                // current script should not be minified
                // thus to preserve scripts order we should apply previous pending minifications and generate its script tag
                // ex: a.js, b.js, c.js, d.js where c should not be minified. script tag generated must be min_a_+_b.js, c.js, min_d.js
                foreach ($minifyJsByParams as $param_value=>$js_files) {
                    foreach (jMinifier::minify( $js_files, 'js' ) as $minifiedJs ) {
                        $this->outputJsScriptTag( $basePath.$minifiedJs, $param_value, JELIX_APP_WWW_PATH.$minifiedJs);
                    }
                }
                // minified operation finished on pending scripts. thus clear js array of scripts to minify :
                $minifyJsByParams = array();

                $this->outputJsScriptTag( $src, $params, JELIX_APP_WWW_PATH.$pathSrc );
            }
        }
        //minify all pending JS script files (may be all files if none was excluded or had absolute URL)
        foreach ($minifyJsByParams as $param_value=>$js_files) {
            foreach (jMinifier::minify( $js_files, 'js' ) as $minifiedJs ) {
                $this->outputJsScriptTag($basePath.$minifiedJs, $param_value, JELIX_APP_WWW_PATH.$minifiedJs);
            }
        }
    }



    final protected function outputCssLinks( &$linkList ) {
        global $gJConfig;

        $minifyCssByParams = array();
        $minifyExcludeCSS = array();

        if( isset($gJConfig->jResponseHtml) && $gJConfig->jResponseHtml['minifyExcludeCSS'] ) {
            $minifyExcludeCSS = explode( ',', $gJConfig->jResponseHtml['minifyExcludeCSS'] );
        }

        $basePath = $gJConfig->urlengine['basePath'];

        foreach ($linkList as $src=>$params){
            //the extra params we may found in there.
            $cssParams = '';
            
            $pathSrc = $src;
            if ( $basePath != '/' && $basePath != '' ) {
                $res = explode($basePath, $src);
                if ( count($res) > 1 )
                    list(,$pathSrc) = $res;
            }

            $pathIsAbsolute = (strpos($pathSrc,'http://')!==FALSE);

            if( isset($gJConfig->jResponseHtml) && $gJConfig->jResponseHtml['minifyCSS'] &&
                ! $pathIsAbsolute && ! in_array(basename($pathSrc), $minifyExcludeCSS) ) {
                //this file should be minified
                $sparams=$params;
                ksort($sparams); //sort to avoid duplicity just because of params order
                foreach ($sparams as $param_name=>$param_value){
                    if( $param_name != "media" ) {
                        $cssParams .= $param_name.'="'. htmlspecialchars($param_value).'" ';
                    }
                }
                if(!isset($params['rel']))
                    $cssParams .='rel="stylesheet" ';
                if( isset($params['media'] ) ) {
                    //split for each media if specified
                    foreach ( explode(',', $params['media']) as $medium) {
                        $myCssParams = $cssParams . 'media="' . $medium . '" ';
                        $minifyCssByParams[$myCssParams][] = "$src";
                    }
                } else {
                    $minifyCssByParams[$cssParams][] = "$src";
                }
            } else {
                // current stylesheet should not be minified
                // thus to preserve stylesheets order we should apply previous pending minifications and generate its link tag
                // ex: a.css, b.css, c.css, d.css where c should not be minified. script tag genrated must be min_a_+_b.css, c.js, min_d.js
                foreach ($minifyCssByParams as $param_value=>$css_files) {
                    foreach (jMinifier::minify( $css_files, 'css' ) as $minifiedCss ) {
                        $this->outputCssLinkTag( $basePath.$minifiedCss, $param_value, JELIX_APP_WWW_PATH.$minifiedCss);
                    }
                }
                $minifyCssByParams = array();
                
                if(!isset($params['rel']))
                    $params['rel'] ='stylesheet';
                
                $this->outputCssLinkTag( $src, $params, JELIX_APP_WWW_PATH.$pathSrc);
            }
        }
        //minify all pending CSS files (may be all files if none was excluded or had absolute URL)
        foreach ($minifyCssByParams as $param_value=>$css_files) {
            foreach (jMinifier::minify( $css_files, 'css' ) as $minifiedCss ) {
                $this->outputCssLinkTag( $basePath.$minifiedCss, $param_value, JELIX_APP_WWW_PATH.$minifiedCss);
            }
        }
    }

    /**
     * generate the content of the <head> content
     */
    final protected function outputHtmlHeader (){
        global $gJConfig;

        echo '<head>'."\n";
        if($this->_isXhtml && $this->xhtmlContentType && strstr($_SERVER['HTTP_ACCEPT'],'application/xhtml+xml')){      
            echo '<meta content="application/xhtml+xml; charset='.$this->_charset.'" http-equiv="content-type"'.$this->_endTag;
        } else {
            echo '<meta content="text/html; charset='.$this->_charset.'" http-equiv="content-type"'.$this->_endTag;
        }
        echo '<title>'.htmlspecialchars($this->title)."</title>\n";

        if(!empty($this->_MetaDescription)){
            // meta description
            $description = implode(' ',$this->_MetaDescription);
            echo '<meta name="description" content="'.htmlspecialchars($description).'" '.$this->_endTag;
        }

        if(!empty($this->_MetaKeywords)){
            // meta description
            $keywords = implode(',',$this->_MetaKeywords);
            echo '<meta name="keywords" content="'.htmlspecialchars($keywords).'" '.$this->_endTag;
        }
        if (!empty($this->_MetaGenerator)) {
            echo '<meta name="generator" content="'.htmlspecialchars($this->_MetaGenerator).'" '.$this->_endTag;
        }
        if (!empty($this->_MetaAuthor)) {
            echo '<meta name="author" content="'.htmlspecialchars($this->_MetaAuthor).'" '.$this->_endTag;
        }

        $this->outputCssLinks( $this->_CSSLink );

        foreach ($this->_CSSIELink as $src=>$params){
            // special params for conditions on IE versions
            if (!isset($params['_ieCondition']))
                $params['_ieCondition'] = 'IE' ;
            echo '<!--[if '.$params['_ieCondition'].' ]>';

            unset($params['_ieCondition']);
            $cssIeLink = array($src=>$params); //make a var to pass it by ref
            $this->outputCssLinks( $cssIeLink );

            echo '<![endif]-->';
        }

        if($this->favicon != ''){
            $fav = htmlspecialchars($this->favicon);
            echo '<link rel="icon" type="image/x-icon" href="',$fav,'" ',$this->_endTag;
            echo '<link rel="shortcut icon" type="image/x-icon" href="',$fav,'" ',$this->_endTag;
        }
        
        // others links
        foreach($this->_Link as $href=>$params){
            $more = array();
            if( !empty($params[1]))
                $more[] = 'type="'.$params[1].'"';
            if (!empty($params[2]))
                $more[] = 'title = "'.htmlspecialchars($params[2]).'"';
            echo '<link rel="',$params[0],'" href="',htmlspecialchars($href),'" ',implode($more, ' '),$this->_endTag;
        }

        // js code
        if(count($this->_JSCodeBefore)){
            echo '<script type="text/javascript">
// <![CDATA[
 '.implode ("\n", $this->_JSCodeBefore).'
// ]]>
</script>';
        }

        $this->outputJsScripts( $this->_JSLink );

        if(count($this->_JSIELink)){
            echo '<!--[if IE]>';

            $this->outputJsScripts( $this->_JSIELink );

            echo '<![endif]-->';
        }

        // styles
        if(count($this->_Styles)){
            echo '<style type="text/css">
            ';
            foreach ($this->_Styles as $selector=>$value){
                if (strlen ($value)){
                    //il y a une paire clef valeur.
                    echo $selector.' {'.$value."}\n";
                }else{
                    //il n'y a pas de valeur, c'est peut être simplement une commande.
                    //par exemple @import qqchose, ...
                    echo $selector, "\n";
                }
            }
            echo "\n </style>\n";
        }
        // js code
        if(count($this->_JSCode)){
            echo '<script type="text/javascript">
// <![CDATA[
 '.implode ("\n", $this->_JSCode).'
// ]]>
</script>';
        }
        echo implode ("\n", $this->_Others), '</head>';
    }

    /**
     * used to erase some head properties
     * @param array $what list of one or many of this strings : 'CSSLink', 'CSSIELink', 'Styles', 'JSLink', 'JSIELink', 'JSCode', 'Others','MetaKeywords','MetaDescription'. If null, it cleans all values.
     */
    final public function clearHtmlHeader ($what=null){
        $cleanable = array ('CSSLink', 'CSSIELink', 'Styles', 'JSLink','JSIELink', 'JSCode', 'Others','MetaKeywords','MetaDescription');
        if($what==null)
            $what= $cleanable;
        foreach ($what as $elem){
            if (in_array ($elem, $cleanable)){
                $name = '_'.$elem;
                $this->$name = array ();
            }
        }
    }

    /**
     * change the type of html for the output
     * @param boolean $xhtml true if you want xhtml, false if you want html
     */
    final public function setXhtmlOutput($xhtml = true){
        $this->_isXhtml = $xhtml;
        if($xhtml)
            $this->_endTag = "/>\n";
        else
            $this->_endTag = ">\n";
    }

    /**
     * activate / deactivate the strict Doctype (activated by default)
     * @param boolean $val true for strict, false for transitional
     * @since 1.1.3
     */
    final public function strictDoctype($val = true){
        $this->_strictDoctype = $val;
    }

    /**
     * says if the response will be xhtml or html
     * @return boolean true if it is xhtml
     */
    final public function isXhtml(){ return $this->_isXhtml; }

    /**
     * return the end of a html tag : "/>" or ">", depending if it will generate xhtml or html
     * @return string
     */
    final public function endTag(){ return $this->_endTag;}

}
