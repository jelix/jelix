<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Loic Mathaud
* @contributor
* @copyright   2005-2006 loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
*
*/
require_once(JELIX_LIB_TPL_PATH.'jTpl.class.php');

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
    * constructor..
    */
    function __construct (){
        global $gJConfig;
        $this->_charset = $gJConfig->defaultCharset;
        $this->content = new jTpl();
    }

    /**
     * generate the xml content and send it to the browser
     * @return boolean    true if ok
     */
    final public function output(){
        $this->_httpHeaders['Content-Type']='text/xml;charset='.$this->_charset;
        $this->sendHttpHeaders();

        echo '<?xml version="1.0" encoding="'. $this->_charset .'"?>', "\n";
        $this->outputXmlHeader();
        $this->_headSent = true;

        // utilisation d'un template
        if (!empty($this->contentTpl)) {
            $xml_string = $this->content->fetch($this->contentTpl);

        // utilisation chaine de caractères xml
        } else {
            $xml_string = $this->content;
        }

        if (simplexml_load_string($xml_string)) {
            echo $xml_string;
        } else {
            // xml mal formé
            return false;
        }

        if ($this->hasErrors()) {
            echo $this->getFormatedErrorMsg();
        }

        return true;
    }

    /**
     * output errors if any
     */
    final public function outputErrors() {
        if (!$this->_headSent) {
             if ($this->_sendHttpHeader) {
                header('Content-Type: text/xml;charset='.$this->_charset);
             }
             echo '<?xml version="1.0" encoding="iso-8859-1"?>';
        }

        echo '<errors xmlns="http://jelix.org/ns/xmlerror/1.0">';
        if ($this->hasErrors()) {
            echo $this->getFormatedErrorMsg();
        } else {
            echo '<error>Unknow Error</error>';
        }
        echo '</errors>';
    }

    /**
     * format error message
     * @return string xml which contains errors description
     */
    protected function getFormatedErrorMsg(){
        $errors = '';
        foreach ($GLOBALS['gJCoord']->errorMessages  as $e) {
            // FIXME : Pourquoi utiliser htmlentities() ?
           $errors .=  '<error type="'. $e[0] .'" code="'. $e[1] .'" file="'. $e[3] .'" line="'. $e[4] .'">'.htmlentities($e[2], ENT_NOQUOTES, $this->_charset). '</error>'. "\n";
        }
        return $errors;
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