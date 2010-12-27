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

        jLog::outputLog($this);

        $HEADBOTTOM = implode("\n", $this->_headBottom);
        $BODYTOP = implode("\n", $this->_bodyTop);
        $BODYBOTTOM = implode("\n", $this->_bodyBottom);

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
