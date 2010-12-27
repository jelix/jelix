<?php
/**
 * @package     jelix
 * @subpackage  core_response
 * @author      Laurent Jouanneau
 * @copyright   2010 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Basic HTML response. the HTML content should be provided by a simple php file.
 * @package  jelix
 * @subpackage core_response
 */
class jResponseBasicHtml extends jResponse {
    /**
    * jresponse id
    * @var string
    */
    protected $_type = 'html';

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
     * says if the document is in xhtml or html
     */
    protected $_isXhtml = true;

    /**
     * says if xhtml content type should be send or not.
     * it true, a verification of HTTP_ACCEPT is done.
     * @var boolean
     */
    public $xhtmlContentType = false;

    /**
     * content for head
     */
    protected $_headBottom  = array ();

    /**#@+
     * content for the body
     * @var array
     */
    protected $_bodyTop = array();
    protected $_bodyBottom = array();
    /**#@-*/

    /**
     * full path of php file to output. it should content php instruction
     * to display these variables:
     * - $HEADBOTTOM: content before th </head> tag
     * - $BODYTOP: content just after the <body> tag, at the top of the page
     * - $BODYBOTTOM: content just before the </body> tag, at the bottom of the page
     * @var string
     */
    public $htmlFile = '';

    /**
    * constructor;
    * setup the charset, the lang
    */
    function __construct (){
        global $gJConfig;
        $this->_charset = $gJConfig->charset;
        $this->_lang = $gJConfig->locale;
        parent::__construct();
    }

    /**
     * add additional content into the document head
     * @param string $content
     * @since 1.0b1
     */
    final public function addHeadContent ($content){
        $this->_headBottom[] = $content;
    }

    /**
     * add content to the body
     * you can add additionnal content, before or after the content of body
     * @param string $content additionnal html content
     * @param boolean $before true if you want to add it before the content, else false for after
     */
    function addContent($content, $before = false){
        if ($before) {
            $this->_bodyTop[]=$content;
        }
        else {
            $this->_bodyBottom[]=$content;
        }
    }

    /**
     *  set the content-type in the http headers
     */
    protected function setContentType() {
        if($this->_isXhtml && $this->xhtmlContentType && strstr($_SERVER['HTTP_ACCEPT'],'application/xhtml+xml')){
            $this->_httpHeaders['Content-Type']='application/xhtml+xml;charset='.$this->_charset;
        }else{
            $this->_httpHeaders['Content-Type']='text/html;charset='.$this->_charset;
        }
    }

    /**
     * output the html content
     *
     * @return boolean    true if the generated content is ok
     */
    public function output(){

        $this->doAfterActions();
        $this->setContentType();
        $this->sendHttpHeaders();

        if ($this->htmlFile == '')
            throw new Exception('static page is missing');

        $HEADBOTTOM = implode("\n", $this->_headBottom);
        $BODYTOP = implode("\n", $this->_bodyTop);
        $BODYBOTTOM = $this->getErrorBarContent();
        $BODYBOTTOM .= implode("\n", $this->_bodyBottom);
        $BODYBOTTOM .= $this->getLogMessageContent();

        include($this->htmlFile);

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
    public function outputErrors(){
        header("HTTP/1.0 500 Internal Server Error");
        header('Content-Type: text/html;charset='.$this->_charset);

        if (file_exists(JELIX_APP_PATH.'response/error.en_US.php'))
            $file = JELIX_APP_PATH.'response/error.en_US.php';
        else
            $file = JELIX_LIB_CORE_PATH.'response/error.en_US.php';
        // we erase already generated content
        $this->_headBottom = array();
        $this->_bodyBottom = array();
        $this->_bodyTop = array();

        jLog::outputLog($this);

        $HEADBOTTOM = implode("\n", $this->_headBottom);
        $BODYTOP = implode("\n", $this->_bodyTop);
        $BODYBOTTOM = implode("\n", $this->_bodyBottom);

        header("HTTP/1.1 500 Internal jelix error");
        header('Content-type: text/html');
        include($file);
    }

    /**
     * create html error messages
     * @return string html content
     */
    protected function getFormatedErrorMsg(){
        $errors='';
        foreach( $GLOBALS['gJCoord']->getErrorMessages()  as $e){
           $errors .=  '<p style="margin:0;"><b>['.$e[0].' '.$e[1].']</b> <span style="color:#FF0000">';
           $errors .= htmlspecialchars($e[2], ENT_NOQUOTES, $this->_charset)."</span> \t".$e[3]." \t".$e[4]."</p>\n";
           if ($e[5])
            $errors.= '<pre>'.htmlspecialchars($e[5], ENT_NOQUOTES, $this->_charset).'</pre>';
        }
        return $errors;
    }

    /**
     * return the html error bar containing errors appeared during the execution of the action
     * @return string
     */
    protected function getErrorBarContent() {
        if (!$this->hasErrors())
            return '';
        $content = '';

        if($GLOBALS['gJConfig']->error_handling['showInFirebug']){
            $content .= '<script type="text/javascript">if(console){';
            foreach( $GLOBALS['gJCoord']->getErrorMessages()  as $e){
                switch ($e[0]) {
                  case 'warning':
                    $content .= 'console.warn("[warning ';
                    break;
                  case 'notice':
                    $content .= 'console.info("[notice ';
                    break;
                  case 'strict':
                    $content .= 'console.info("[strict ';
                    break;
                  case 'error':
                    $content .= 'console.error("[error ';
                    break;
                }
                $m = $e[2]. ($e[5]?"\n".$e[5]:"");
                $content .= $e[1].'] '.str_replace(array('"',"\n","\r","\t"),array('\"','\\n','\\r','\\t'),$m).' ('.str_replace('\\','\\\\',$e[3]).' '.$e[4].')");';
            }
            $content .= '}else{alert("there are some errors, you should activate Firebug to see them");}</script>';
        }else{
            $content .= '<div id="jelixerror" style="position:absolute;left:0px;top:0px;border:3px solid red; background-color:#f39999;color:black;z-index:100;">';
            $content .= $this->getFormatedErrorMsg();
            $content .= '<p><a href="#" onclick="document.getElementById(\'jelixerror\').style.display=\'none\';return false;">close</a></p></div>';
        }

        return $content;
    }

    /**
     *  return the HTML content to display log messages that have 'response' as target
     *  @return string
     */
    protected function getLogMessageContent() {
        if(!count($GLOBALS['gJCoord']->logMessages))
            return '';
        $content = '';
        if(count($GLOBALS['gJCoord']->logMessages['response'])) {
            $content .= '<ul id="jelixlog">';
            foreach($GLOBALS['gJCoord']->logMessages['response'] as $m) {
                $content .= '<li>'.htmlspecialchars($m).'</li>';
            }
            $content .= '</ul>';
        }
        if(count($GLOBALS['gJCoord']->logMessages['firebug'])) {
            $content .= '<script type="text/javascript">if(console){';
            foreach($GLOBALS['gJCoord']->logMessages['firebug'] as $m) {
                $content .= 'console.debug("'.str_replace(array('"',"\n","\r","\t"),array('\"','\\n','\\r','\\t'),$m).'");';
            }
            $content .= '}else{alert("there are log messages, you should activate Firebug to see them");}</script>';
        }
        return $content;
    }


    /**
     * change the type of html for the output
     * @param boolean $xhtml true if you want xhtml, false if you want html
     */
    public function setXhtmlOutput($xhtml = true){
        $this->_isXhtml = $xhtml;
    }


    /**
     * says if the response will be xhtml or html
     * @return boolean true if it is xhtml
     */
    final public function isXhtml(){ return $this->_isXhtml; }

}
