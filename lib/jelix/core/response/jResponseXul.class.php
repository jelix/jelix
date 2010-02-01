<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Laurent Jouanneau
* @contributor Dominique Papin, Julien Issler
* @copyright   2005-2009 Laurent Jouanneau, 2007 Dominique Papin
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
*
*/
require_once(JELIX_LIB_PATH.'tpl/jTpl.class.php');

/**
* Generate a XUL window
* @package  jelix
* @subpackage core_response
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
    function __construct (){
        $this->body = new jTpl();
        parent::__construct();
    }

    /**
     * generate the xul content.
     * @return boolean    true if it's ok
     */
    public function output(){
        $this->_headSent = false;

        $this->_httpHeaders['Content-Type']='application/vnd.mozilla.xul+xml;charset='.$GLOBALS['gJConfig']->charset;
        $this->sendHttpHeaders();
        $this->doAfterActions();
        if($this->bodyTpl != '')
            $this->body->meta($this->bodyTpl);
        $this->outputHeader();
        $this->_headSent = true;
        echo implode('',$this->_bodyTop);
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
                echo '<vbox id="jelixerror" style="border:3px solid red; background-color:#f39999;color:black;">';
                echo $this->getFormatedErrorMsg();
                echo '</vbox>';
            }
        }

        echo implode('',$this->_bodyBottom);

        if(count($GLOBALS['gJCoord']->logMessages)) {
            if(count($GLOBALS['gJCoord']->logMessages['response'])) {
                echo '<vbox id="jelixlog">';
                foreach($GLOBALS['gJCoord']->logMessages['response'] as $m) {
                    echo '<description>',htmlspecialchars($m),'</description>';
                }
                echo '</vbox>';
            }
            if(count($GLOBALS['gJCoord']->logMessages['firebug'])) {
                echo '<script type="text/javascript">if(console){';
                foreach($GLOBALS['gJCoord']->logMessages['firebug'] as $m) {
                    echo 'console.debug("',str_replace(array('"',"\n","\r","\t"),array('\"','\\n','\\r','\\t'),$m),'");';
                }
                echo '}else{alert("there are log messages, you should activate Firebug to see them");}</script>';
            }
        }

        echo '</',$this->_root,'>';
        return true;
    }

    public function outputErrors(){
        if(!$this->_headSent){
            header("HTTP/1.0 500 Internal Server Error");
            header('Content-Type: application/vnd.mozilla.xul+xml;charset='.$GLOBALS['gJConfig']->charset);
            echo '<?xml version="1.0" encoding="'.$GLOBALS['gJConfig']->charset.'" ?>'."\n";
            echo '<',$this->_root,' title="Errors" xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">';
        }
        echo '<vbox>';
        if($this->hasErrors()){
            echo $this->getFormatedErrorMsg();
        }else{
            echo "<description style=\"color:#FF0000;\">Unknown error</description>";
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
            $errors .=  '<description style="color:#FF0000;">['.$e[0].' '.$e[1].'] ';
            $errors .= htmlspecialchars($e[2], ENT_NOQUOTES, $GLOBALS['gJConfig']->charset)." \t".$e[3]." \t".$e[4]."</description>\n";
            if ($e[5])
              $errors .= '<div xmlns="http://www.w3.org/1999/xhtml"><pre>'.htmlspecialchars($e[5], ENT_NOQUOTES, $GLOBALS['gJConfig']->charset).'</pre></div>';
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
        $charset = $GLOBALS['gJConfig']->charset;

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
            echo '<script type="application/x-javascript" src="',htmlspecialchars($src),'" />',"\n";
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
     * The method you can overload in your inherited XUL response
     * overload it if you want to add processes (stylesheet, head settings, additionnal content etc..)
     * after all actions
     * @since 1.1
     */
    protected function doAfterActions(){
    }

    /**
     *
     */
    protected function _otherthings(){
        // overlays

        // browser sniffing, because "&" should be escaped in a xul-overlay PI in gecko 1.9+
        $escape = false;
        if(preg_match('!^Mozilla/5.0 \(.* rv:(\d)\.(\d).*\) Gecko/\d+.*$!',$_SERVER["HTTP_USER_AGENT"],$m)){
            if(version_compare($m[1].'.'.$m[2], '1.9') >= 0) {
                $escape = true;
            }
        }

        if($this->fetchOverlays){
            $sel = new jSelectorTpl($this->bodyTpl);
            $eventresp = jEvent::notify ('FetchXulOverlay', array('tpl'=>$sel->toString()));
            foreach($eventresp->getResponse() as $rep){
                if(is_array($rep)){
                    $this->_overlays[jUrl::get($rep[0],$rep[1])]=true;
                }elseif(is_string($rep)){
                    $this->_overlays[jUrl::get($rep)]=true;
                }
            }
        }

        foreach ($this->_overlays as $src=>$ok){
            echo  '<?xul-overlay href="',($escape?htmlspecialchars($src):$src),'" ?>',"\n";
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
