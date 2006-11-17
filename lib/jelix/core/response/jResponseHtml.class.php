<?php
/**
* @package     jelix
* @subpackage  core
* @author      Jouanneau Laurent
* @contributor Yann (description and keywords)
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*
* few lines of code are copyrighted CopixTeam http://www.copix.org
*/

/**
*
*/
require_once(JELIX_LIB_TPL_PATH.'jTpl.class.php');

/**
* HTML response
* @package  jelix
* @subpackage core
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
     * says that the <head> content has been send
     * @var boolean
     */
    protected $_headSent = false;

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
    private $_CSSLink = array ();
    private $_Styles  = array ();
    private $_JSLink  = array ();
    private $_JSCode  = array ();
    private $_Others  = array ();
    private $_MetaKeywords = array();
    private $_MetaDescription = array();
    /**
     * content for the body
     */
    private $_bodyTop = array();
    private $_bodyBottom = array();

    /**
     * says if the document is in xhtml or html
     */
    protected $_isXhtml = true;
    protected $_endTag="/>\n";


    /**
    * constructor; 
    * setup the charset, the lang, the template engine
    */
    function __construct ($attributes=array()){
        global $gJConfig;
        $this->_charset = $gJConfig->defaultCharset;
        $this->_lang = $gJConfig->defaultLocale;
        $this->body = new jTpl();
        parent::__construct($attributes);
    }

    /**
     * output the html content
     * 
     * @return boolean    true if the generated content is ok
     */
    final public function output(){
        $this->_headSent = false;
        $this->_httpHeaders['Content-Type']='text/html;charset='.$this->_charset;

        $this->sendHttpHeaders();
        if($this->_isXhtml){
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="',$this->_lang,'" lang="',$this->_lang,'">
';
        }else{
            echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">', "\n";
            echo '<html lang="',$this->_lang,'">';
        }
        $this->_commonProcess();
        if($this->bodyTpl != '')
           $this->body->meta($this->bodyTpl);
        $this->outputHtmlHeader();
        echo '<body ';
        foreach($this->bodyTagAttributes as $attr=>$value){
           echo $attr,'="', htmlspecialchars($value),'" ';
        }
        echo ">\n";
        $this->_headSent = true;
        echo implode("\n",$this->_bodyTop);
        if($this->bodyTpl != '')
           $this->body->display($this->bodyTpl);

        if($this->hasErrors()){
            echo '<div id="jelixerror" style="position:absolute;left:0px;top:0px;border:3px solid red; background-color:#f39999;color:black;">';
            echo $this->getFormatedErrorMsg();
            echo '<p><a href="#" onclick="document.getElementById(\'jelixerror\').style.display=\'none\';return false;">fermer</a></p></div>';
        }
        echo implode("\n",$this->_bodyBottom);
        echo '</body></html>';
        return true;
    }

    /**
     * The method you can overload in your inherited html response
     * overload it if you want to add processes (stylesheet, head settings, additionnal content etc..)
     * for all actions
     */
    protected function _commonProcess(){

    }

    /**
     * output errors
     */
    final public function outputErrors(){
        if(!$this->_headSent){
             if(!$this->_httpHeadersSent) header('Content-Type: text/html;charset='.$this->_charset);
            echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">', "\n";
            echo '<html><head><title>Errors</title></head><body>';
        }
        if($this->hasErrors()){
            echo $this->getFormatedErrorMsg();
        }else{
            echo '<p style="color:#FF0000">Unknow Error</p>';
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
            // FIXME : Pourquoi utiliser htmlentities() ?
           $errors .=  '<p style="margin:0;"><b>['.$e[0].' '.$e[1].']</b> <span style="color:#FF0000">'.htmlentities($e[2], ENT_NOQUOTES, $this->_charset)."</span> \t".$e[3]." \t".$e[4]."</p>\n";
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
     * add a link to a javascript script in the document head
     * @param string $src the link
     * @param array $params additionnals attributes for the script tag
     */
    final public function addJSLink ($src, $params=array()){
        if (!isset ($this->_JSLink[$src])){
            $this->_JSLink[$src] = $params;
        }
    }

    /**
     * add a link to a css stylesheet in the document head
     * @param string $src the link
     * @param array $params additionnals attributes for the link tag
     */
    final public function addCSSLink ($src, $params=array ()){
        if (!isset ($this->_CSSLink[$src])){
            $this->_CSSLink[$src] = $params;
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
     * @deprecated 
     * @see $addHeadContent
     */
    final public function addOthers ($content){
        $this->_Others[] = $content;
    }

    /**
     * add additional content into the document head
     * @param string $content
     */
    final public function addHeadContent ($content){
        $this->_Others[] = $content;
    }

    /**
     * add inline javascript code (inside a <script> tag)
     * @param string $code  javascript source code
     */
    final public function addJSCode ($code){
        $this->_JSCode[] = $code;
    }

    /**
     * add some keywords in a keywords meta tag
     * @param string $content keywords
     */
    final public function addMetaKeywords ($content){
        $this->_MetaKeywords[] = $content;
    }
    /**
     * add a description in a description meta tag
     * @param string $content a description
     */
    final public function addMetaDescription ($content){
        $this->_MetaDescription[] = $content;
    }

    /**
     * generate the content of the <head> content
     */
    final protected function outputHtmlHeader (){
        echo '<head>'."\n";
        echo '<meta content="text/html; charset='.$this->_charset.'" http-equiv="content-type"'.$this->_endTag;
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

        // css link
        foreach ($this->_CSSLink as $src=>$params){
            //the extra params we may found in there.
            $more = '';
            foreach ($params as $param_name=>$param_value){
                $more .= $param_name.'="'. htmlspecialchars($param_value).'" ';
            }
            echo  '<link rel="stylesheet" type="text/css" href="',$src,'" ',$more,$this->_endTag;
        }

        // js link
        foreach ($this->_JSLink as $src=>$params){
            //the extra params we may found in there.
            $more = '';
            foreach ($params as $param_name=>$param_value){
                $more .= $param_name.'="'. htmlspecialchars($param_value).'" ';
            }
            echo '<script type="text/javascript" src="',$src,'" ',$more,'></script>';
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
     * @param array $what list of one or many of this strings : 'CSSLink', 'Styles', 'JSLink', 'JSCode', 'Others','MetaKeywords','MetaDescription'
     */
    final public function clearHtmlHeader ($what){
        $cleanable = array ('CSSLink', 'Styles', 'JSLink', 'JSCode', 'Others','MetaKeywords','MetaDescription');
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
?>