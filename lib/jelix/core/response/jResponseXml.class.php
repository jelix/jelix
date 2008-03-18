<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Loic Mathaud
* @contributor Laurent Jouanneau
* @contributor Sylvain de Vathaire
* @copyright   2005-2006 loic Mathaud
* @copyright   2007 Laurent Jouanneau
* @copyright   2008 Sylvain de Vathaire
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
*
*/
require_once(JELIX_LIB_PATH.'tpl/jTpl.class.php');

/**
* XML response generator
* @package  jelix
* @subpackage core_response
*/
class jResponseXml extends jResponse {
    /**
    * Id of the response
    * @var string
    */
    protected $_type = 'xml';


    /**
     * the template container
     * @var jTpl
     */
    public $content = null;

    /**
     * selector of the template file
     * @var string
     */
    public $contentTpl = '';

    /**
     * The charset
     * @var string
     */
    protected $_charset;

    private $_css = array();
    private $_xsl = array();

    /**
     * says what part of the html head has been send
     * @var integer
     */
    protected $_headSent = 0;

    /** 
     * say if the XML header have to be generated
     * Usefull if the XML string to output already contain the XML header
     * @var boolean
     * @since 1.0.3
     */
    public $sendXMLHeader = TRUE;


    /**
    * constructor..
    */
    function __construct (){
        global $gJConfig;
        $this->_charset = $gJConfig->charset;
        $this->content = new jTpl();
        parent::__construct();
    }

    /**
     * generate the xml content and send it to the browser
     * @return boolean    true if ok
     */
    final public function output(){
        $this->_httpHeaders['Content-Type']='text/xml;charset='.$this->_charset;
        $this->sendHttpHeaders();

        if($this->sendXMLHeader){
            echo '<?xml version="1.0" encoding="'. $this->_charset .'"?>', "\n";
            $this->outputXmlHeader();
        }
        $this->_headSent = true;


        if(is_string($this->content)) {
            // utilisation chaine de caractères xml
            $xml_string = $this->content;
        }else if (!empty($this->contentTpl)) {
            // utilisation d'un template
            $xml_string = $this->content->fetch($this->contentTpl);
        }else{
            throw new jException('jelix~errors.repxml.no.content');
        }

        if (simplexml_load_string($xml_string)) {
            echo $xml_string;
        } else {
            // xml mal formé
            throw new jException('jelix~errors.repxml.invalid.content');
        }
        return true;
    }

    /**
     * output errors if any
     */
    final public function outputErrors() {
        if (!$this->_headSent) {
            if (!$this->_httpHeadersSent) {
                header("HTTP/1.0 500 Internal Server Error");
                header('Content-Type: text/xml;charset='.$this->_charset);
            }
            echo '<?xml version="1.0" encoding="'. $this->_charset .'"?>';
        }

        echo '<errors xmlns="http://jelix.org/ns/xmlerror/1.0">';
        if ($this->hasErrors()) {
            foreach ($GLOBALS['gJCoord']->errorMessages  as $e) {
                echo '<error xmlns="http://jelix.org/ns/xmlerror/1.0" type="'. $e[0] .'" code="'. $e[1] .'" file="'. $e[3] .'" line="'. $e[4] .'">'.htmlspecialchars($e[2], ENT_NOQUOTES, $this->_charset). '</error>'. "\n";
            }
        } else {
            echo '<error>Unknow Error</error>';
        }
        echo '</errors>';
    }

    /**
     * to add a link to css stylesheet
     * @since 1.0b1
     */
    public function addCSSStyleSheet($src, $params = array()) {
        if (!isset($this->_css[$src])) {
            $this->_css[$src] = $params;
        }
    }

    /**
     * to add a link to an xsl stylesheet
     * @since 1.0b1
     */
    public function addXSLStyleSheet($src, $params = array()) {
        if (!isset($this->_xsl[$src])) {
            $this->_xsl[$src] = $params;
        }
    }

    /**
     * output all processing instructions (stylesheet, xsl..) before the XML content
     */
    protected function outputXmlHeader() {
        // XSL stylesheet
        foreach ($this->_xsl as $src => $params) {
            //the extra params we may found in there.
            $more = '';
            foreach ($params as $param_name => $param_value) {
                $more .= $param_name.'="'. htmlspecialchars($param_value).'" ';
            }
            echo ' <?xml-stylesheet type="text/xsl" href="', $src,'" ', $more,' ?>';
        }

        // CSS stylesheet
        foreach ($this->_css as $src => $params) {
            //the extra params we may found in there.
            $more = '';
            foreach ($params as $param_name => $param_value) {
                $more .= $param_name.'="'. htmlspecialchars($param_value).'" ';
            }
            echo ' <?xml-stylesheet type="text/css" href="', $src,'" ', $more,' ?>';
        }
    }
}

?>