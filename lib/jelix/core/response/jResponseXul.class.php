<?php
/**
* @package     jelix
* @subpackage  core
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
*
*/
require_once(JELIX_LIB_TPL_PATH.'jTpl.class.php');

/**
* Generate a XUL window
* @package  jelix
* @subpackage core
* @see jResponse
*/
class jResponseXul extends jResponse {
    /**
    * @var string
    */
    protected $_type = 'xul';

    /**
     * header content : list of overlay links
     * @var array
     */
    protected $_overlays  = array ();
    /**
     * header content : list of css file links
     * @var array
     */
    protected $_CSSLink = array ();
    /**
     * header content : list of javascript file links
     * @var array
     */
    protected $_JSLink  = array ();
    /**
     * header content : list of piece of javascript code
     * @var array
     */
    protected $_JSCode  = array ();

    /**
     * root tag name.
     * could be override into child class for other xul document
     */
    protected $_root = 'window';

    /**
     * list of attributes and their values for the root element
     * @var array
     */
    public $rootAttributes= array();

    /**
     * Title of the window
     * @var string
     */
    public $title = '';

    /**
     * template engine to generate the window content
     * @var jTpl
     */
    public $body = null;

    /**
     * selector of the template to use
     * @var string
     */
    public $bodyTpl = '';

    /**
     * selector of the template to use for errors
     * @var string
     */
    public $bodyErrorTpl = '';

    /**
     * says if an event is sent to retrieve overlays url for the xul content
     * @var boolean
     */
    public $fetchOverlays=false;

    protected $_bodyTop = array();
    protected $_bodyBottom = array();
    protected $_headSent = false;

    /**
    * constructor
    */
    function __construct ($attributes=array()){
        $this->body = new jTpl();
        parent::__construct($attributes);
    }

    /**
     * generate the xul content.
     * @return boolean    true if it's ok
     */
    public function output(){
        $this->_headSent = false;

        $this->_httpHeaders['Content-Type']='application/vnd.mozilla.xul+xml;charset='.$GLOBALS['gJConfig']->defaultCharset;
        $this->sendHttpHeaders();
        $this->_commonProcess();
        if($this->bodyTpl != '')
           $this->body->meta($this->bodyTpl);
        $this->outputHeader();
        $this->_headSent = true;
        echo implode('',$this->_bodyTop);
        if($this->bodyTpl != '')
            $this->body->display($this->bodyTpl);
        if($this->hasErrors()){
            echo '<vbox id="copixerror" style="border:3px solid red; background-color:#f39999;color:black;">';
            echo $this->getFormatedErrorMsg();
            echo '</vbox>';
        }
        echo implode('',$this->_bodyBottom);
        echo '</',$this->_root,'>';
        return true;
    }

    public function outputErrors(){
        if(!$this->_headSent){
            header('Content-Type: application/vnd.mozilla.xul+xml;charset='.$GLOBALS['gJConfig']->defaultCharset);
            echo '<?xml version="1.0" encoding="'.$GLOBALS['gJConfig']->defaultCharset.'" ?>'."\n";
            echo '<',$this->_root,' title="Erreurs" xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';
        }
        echo '<vbox>';
        if($this->hasErrors()){
           echo $this->getFormatedErrorMsg();
        }else{
           echo "<description style=\"color:#FF0000;\">Unknow error</description>";
        }
        echo '</vbox></',$this->_root,'>';
    }

    /**
     *
     * @return string formated errors
     */
    protected function getFormatedErrorMsg(){
        $errors='';
        foreach( $GLOBALS['gJCoord']->errorMessages  as $e){
            // FIXME : Pourquoi utiliser htmlentities() ?
           $errors .=  '<description style="color:#FF0000;">['.$e[0].' '.$e[1].'] '.htmlspecialchars($e[2], ENT_NOQUOTES, $GLOBALS['gJConfig']->defaultCharset)." \t".$e[3]." \t".$e[4]."</description>\n";
        }
        return $errors;
    }

    /**
     * call it to add manually content before or after the main content
     * @param string $content xul content
     * @param boolean $beforeTpl true if you want to add before, false for after
     */
    function addContent($content, $beforeTpl = false){
      if($beforeTpl){
        $this->_bodyTop[]=$content;
      }else{
         $this->_bodyBottom[]=$content;
      }
    }

    /**
     * add a link to a xul overlay for the xul page
     * @param string $src url of a xul overlay
     */
    function addOverlay ($src){
        $this->_overlays[$src] = true;
    }
    /**
     * add a link to a javascript file
     * @param string $src url
     */
    function addJSLink ($src, $params=array()){
        if (!isset ($this->_JSLink[$src])){
            $this->_JSLink[$src] = $params;
        }
    }
    /**
     * add a link to a css stylesheet
     * @param string $src url
     */
    function addCSSLink ($src, $params=array ()){
        if (!isset ($this->_CSSLink[$src])){
            $this->_CSSLink[$src] = $params;
        }
    }

    /**
     * add a piece of javascript code
     * @param string $code javascript source code
     */
    function addJSCode ($code){
        $this->_JSCode[] = $code;
    }

    protected function outputHeader (){
        $charset = $GLOBALS['gJConfig']->defaultCharset;

        echo '<?xml version="1.0" encoding="'.$charset.'" ?>'."\n";

        // css link
        foreach ($this->_CSSLink as $src=>$param){
            if(is_string($param))
                echo  '<?xml-stylesheet type="text/css" href="',htmlspecialchars($src,ENT_COMPAT, $charset),'" '.$param.'?>',"\n";
            else
                echo  '<?xml-stylesheet type="text/css" href="',htmlspecialchars($src,ENT_COMPAT, $charset),'" ?>',"\n";
        }
        $this->_otherthings();

        echo '<',$this->_root;
        foreach($this->rootAttributes as $name=>$value){
            echo ' ',$name,'="',htmlspecialchars($value,ENT_COMPAT, $charset),'"';
        }
        echo "  xmlns:html=\"http://www.w3.org/1999/xhtml\"
        xmlns=\"http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul\">\n";

        // js link
        foreach ($this->_JSLink as $src=>$params){
            echo '<script type="application/x-javascript" src="',$src,'" />',"\n";
        }


        // js code
        if(count($this->_JSCode)){
            echo '<script type="application/x-javascript">
<![CDATA[
 '.implode ("\n", $this->_JSCode).'
]]>
</script>';
        }
    }

    /**
     * override it into your own xul response object, to do
     * all things commons to all xul actions
     */
    protected function _commonProcess(){

    }

    /**
     *
     */
    protected function _otherthings(){
        // overlays
        if($this->fetchOverlays){
            $eventresp = jEvent::notify ('FetchXulOverlay', array('tpl'=>$this->bodyTpl));
            foreach($eventresp->getResponse() as $rep){
                if(is_array($rep)){
                    $this->_overlays[jUrl::get($rep[0],$rep[1])]=true;
                }elseif(is_string($rep)){
                    $this->_overlays[jUrl::get($rep)]=true;
                }
            }
        }

        foreach ($this->_overlays as $src=>$ok){
            echo  '<?xul-overlay href="',$src,'" ?>',"\n";
        }

        $this->rootAttributes['title']=$this->title;
    }

    /**
     * clear all header informations
     * @var array list of keyword
     */
    public function clearHeader ($what){
        $cleanable = array ('CSSLink', 'JSLink', 'JSCode', 'overlays');
        foreach ($what as $elem){
            if (in_array ($elem, $cleanable)){
                $name = '_'.$elem;
                $this->$name = array ();
            }
        }
    }

    public function getFormatType(){ return 'xul';}
}

?>
