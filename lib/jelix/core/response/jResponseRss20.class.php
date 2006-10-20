<?php
/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Loic Mathaud
* @contributor
* @copyright   2005-2006 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
*
*/
require_once(JELIX_LIB_TPL_PATH.'jTpl.class.php');
require_once(JELIX_LIB_UTILS_PATH.'jRss20Item.class.php');
/**
* Rss2.0 response
* @package  jelix
* @subpackage core
*/
class jResponseRss20 extends jResponse {
    protected $_type = 'rss2.0';
    
    /**
    * Mandatory title element in <channel>
    */
    public $title = '';
    
    /**
    * Mandatory link element in <channel>
    */
    public $link = '';
    
    /**
    * Mandatory description element in <channel>
    */
    public $description = '';
    
    /**
    * Others optional elements in <channel>
    */
    protected $_optionals = array();
    
    protected $_lang;
    protected $_charset;
    
    private $_xsl = array();
    
    /**
    *
    */
    public $items = null;
    
    protected $item_list;
    
    /**
     * Template selector
     * The content of the templates only deals with <item>
     * @var string
     */
    private $itemsTpl = 'jelix~rss20items';
    
    
    function __construct($attributes = array()) {
        global $gJConfig;
        $this->_charset = $gJConfig->defaultCharset;
        $this->_lang = $gJConfig->defaultLocale;
        $this->items = new jTpl();
        parent::__construct($attributes);
    }
    
    /**
     * Generate the content and send it
     * Errors are managed
     * @return boolean true if generation is ok, else false
     */
    final public function output() {
        $this->_headSent = false;
        $this->_httpHeaders['Content-Type']='application/xml;charset='.$this->_charset;

        $this->sendHttpHeaders();
        echo '<?xml version="1.0" encoding="'. $this->_charset .'"?>', "\n";
        $this->_outputXmlHeader();
        $this->_headSent = true;
        echo '<rss version="2.0">', "\n";
        echo '<channel>', "\n";
        echo '<title>'. $this->title .'</title>', "\n";
        echo '<link>'. $this->link .'</link>', "\n";
        echo '<description>'. $this->description .'</description>', "\n";
        echo '<language>'. str_replace('_', '-', strtolower($this->_lang)) .'</language>', "\n";
        $this->_outputOptionals();

        if ($this->itemsTpl != '') {
            $this->items->assign('items', $this->item_list);
            $this->items->display($this->itemsTpl);
        }

        if ($this->hasErrors()) {
            echo $this->getFormatedErrorMsg();
        }
        echo '</channel>', "\n";
        echo '</rss>';
        return true;
    }
    
    final public function outputErrors() {
        if (!$this->_headSent) {
             if ($this->_sendHttpHeader) {
                header('Content-Type: text/xml;charset='.$this->_charset);
             }
             echo '<?xml version="1.0" encoding="'. $this->_charset .'"?>';
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
     * Format error messages
     * @return string formated errors
     */
    protected function getFormatedErrorMsg(){
        $errors = '';
        foreach ($GLOBALS['gJCoord']->errorMessages  as $e) {
            // FIXME : Pourquoi utiliser htmlentities() ?
           $errors .=  '<error type="'. $e[0] .'" code="'. $e[1] .'" file="'. $e[3] .'" line="'. $e[4] .'">'.htmlentities($e[2], ENT_NOQUOTES, $this->_charset). '</error>'. "\n";
        }
        return $errors;
    }
    
    public function addOptionals($content) {
        if (is_array($content)) {
            $this->_optionals = $content;
        }
    }
    
    public function addXSLStyleSheet($src, $params=array ()) {
        if (!isset($this->_xsl[$src])){
            $this->_xsl[$src] = $params;
        }
    }
    
    public function addItem($item) {
        $this->item_list[] = $item;
    }
    
    protected function _outputXmlHeader() {
        // XSL stylesheet
        foreach ($this->_xsl as $src => $params) {
            //the extra params we may found in there.
            $more = '';
            foreach ($params as $param_name => $param_value) {
                $more .= $param_name.'="'. htmlspecialchars($param_value).'" ';
            }
            echo ' <?xml-stylesheet type="text/xsl" href="', $src,'" ', $more,' ?>';
        }
    }
    
    protected function _outputOptionals() {
        if (is_array($this->_optionals)) {
            foreach ($this->_optionals as $name => $value) {
                echo '<'. $name .'>'. $value .'</'. $name .'>', "\n";
            }
        }
    }
}

?>
