<?php

/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Loic Mathaud
* @contributor
* @copyright   2005-2006 loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*
* Some line of code are copyrighted CopixTeam http://www.copix.org
*/

require_once(JELIX_LIB_TPL_PATH.'jTpl.class.php');

/**
* Générateur de réponse HTML
* @package  jelix
* @subpackage core
*/
class jResponseXml extends jResponse {
    /**
    * identifiant du générateur de sortie
    * @var string
    */
    protected $_type = 'xml';


    /**
     * @var jTpl
     */
    public $content = null;

    /**
     * selecteur du template principal
     * le contenu du template principal concerne le contenu
     */
    public $contentTpl = '';


    protected $_charset;


    /**
    * Contruction et initialisation
    */
    function __construct (){
        global $gJConfig;
        $this->_charset = $gJConfig->defaultCharset;
        $this->content = new jTpl();
    }

    /**
     * génère le contenu et l'envoi au navigateur.
     * Il doit tenir compte des erreurs
     * @return boolean    true si la génération est ok, false sinon
     */
    final public function output(){
        $this->_httpHeaders['Content-Type']='text/xml;charset='.$this->_charset;
        $this->sendHttpHeaders();

        echo '<?xml version="1.0" encoding="'. $this->_charset .'"?>', "\n";
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
     * formate les messages d'erreurs
     * @return string les erreurs formatées
     */
    protected function getFormatedErrorMsg(){
        $errors = '';
        foreach ($GLOBALS['gJCoord']->errorMessages  as $e) {
            // FIXME : Pourquoi utiliser htmlentities() ?
           $errors .=  '<error type="'. $e[0] .'" code="'. $e[1] .'" file="'. $e[3] .'" line="'. $e[4] .'">'.htmlentities($e[2], ENT_NOQUOTES, $this->_charset). '</error>'. "\n";
        }
        return $errors;
    }
}
?>
