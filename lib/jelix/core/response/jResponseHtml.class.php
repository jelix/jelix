<?php

/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*
* Some line of code are copyrighted CopixTeam http://www.copix.org
*/

require_once(JELIX_LIB_TPL_PATH.'jTpl.class.php');

/**
* Générateur de réponse HTML
*/

class jResponseHtml extends jResponse {
    /**
    * identifiant du générateur de sortie
    * @var string
    */
    protected $_type = 'html';


    public $title = '';

    /**
     * @var jTpl
     */

    public $body = null;

    /**
     * selecteur du template principal
     * le contenu du template principal concerne le contenu de <body>
     */
    public $bodyTpl = '';

    /**
     * template principal à afficher en cas d'erreur
     */
    public $bodyErrorTpl = '';

    /**
     * attribut du body
     */
    public $bodyTagAttributes= array();

    /**
     * indique que le <head> a déjà été envoyé (pour les erreurs)
     */
    protected $_headSent = false;

    protected $_charset;
    protected $_lang;
    /**
     * contenu pour le header
     */
    private $_CSSLink = array ();
    private $_Styles  = array ();
    private $_JSLink  = array ();
    private $_JSCode  = array ();
    private $_Others  = array ();
    /**
     * contenu pour le body
     */
    private $_bodyTop = array();
    private $_bodyBottom = array();

    /**
     * indique si on veut générer du XHTML ou du HTML
     */
    protected $_isXhtml = true;
    protected $_endTag="/>\n";


    /**
    * Contruction et initialisation
    */
    function __construct ($attributes=array()){
        global $gJConfig;
        $this->_charset = $gJConfig->defaultCharset;
        $this->_lang = $gJConfig->defaultLocale;
        $this->body = new jTpl();
        parent::__construct($attributes);
    }

    /**
     * génère le contenu et l'envoi au navigateur.
     * Il doit tenir compte des erreurs
     * @return boolean    true si la génération est ok, false sinon
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
            echo '<p><a href="#" onclick="document.getElementById(\'jelixerror\').style.display=\'none\';return false;">fermer</a></div>';
        }
        echo implode("\n",$this->_bodyBottom);
        echo '</body></html>';
        return true;
    }

    // à surcharger dans les classes héritières
    protected function _commonProcess(){

    }

    final public function outputErrors(){
        if(!$this->_headSent){
             if($this->_sendHttpHeader) header('Content-Type: text/html;charset='.$this->_charset);
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
     * formate les messages d'erreurs
     * @return string les erreurs formatées
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
     * methode pour ajouter du contenu avant/aprés le contenu du body
     */

    function addContent($content, $beforeTpl = false){
      if($beforeTpl){
        $this->_bodyTop[]=$content;
      }else{
         $this->_bodyBottom[]=$content;
      }
    }


    /**
     * méthodes pour manipuler le header
     */


    final public function addJSLink ($src, $params=array()){
        if (!isset ($this->_JSLink[$src])){
            $this->_JSLink[$src] = $params;
        }
    }
    final public function addCSSLink ($src, $params=array ()){
        if (!isset ($this->_CSSLink[$src])){
            $this->_CSSLink[$src] = $params;
        }
    }
    final public function addStyle ($selector, $def=null){
        if (!isset ($this->_Styles[$selector])){
            $this->_Styles[$selector] = $def;
        }
    }
    final public function addOthers ($content){
        $this->_Others[] = $content;
    }

    final public function addJSCode ($code){
        $this->_JSCode[] = $code;
    }

    final protected function outputHtmlHeader (){
        echo '<head><title>'.$this->title."</title>\n";
        echo '<meta content="text/html; charset='.$this->_charset.'" http-equiv="content-type"'.$this->_endTag;

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


    final public function clearHtmlHeader ($what){
        $cleanable = array ('CSSLink', 'Styles', 'JSLink', 'JSCode', 'Others');
        foreach ($what as $elem){
            if (in_array ($elem, $cleanable)){
                $name = '_'.$elem;
                $this->$name = array ();
            }
        }
    }

    final public function setXhtmlOutput($xhtml = true){
       $this->_isXhtml = $xhtml;
       if($xhtml)
          $this->_endTag = "/>\n";
       else
          $this->_endTag = ">\n";
    }

    final public function isXhtml(){ return $this->_isXhtml; }
    final public function endTag(){ return $this->_endTag;}

}
?>
