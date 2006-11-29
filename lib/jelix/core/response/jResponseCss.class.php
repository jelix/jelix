<?php
/**
* @package     jelix
* @subpackage  core
* @author      Nicolas Jeudy
* @contributor
* @copyright   2006 Nicolas Jeudy
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Générateur de réponse Css
* @package  jelix
* @subpackage core
*/
class jResponseCss extends jResponse {

    /**
    * identifiant du générateur de sortie
    * @var string
    */
    protected $_type = 'css';

    /**
     * contenu
     * @var string
     */
    public $content = '';


    /**
    * Contruction et initialisation
    */
    function __construct ($attributes=array()){
        parent::__construct($attributes);
    }

    /**
     * génère le contenu et l'envoi au navigateur.
     * @return boolean    true si la génération est ok, false sinon
     */
    public function output(){
        global $gJConfig;
        $this->_httpHeaders['Content-Type']='text/css;charset='.$gJConfig->defaultCharset;
        $this->_httpHeaders['Content-length']=strlen($this->content);
        $this->sendHttpHeaders();
        echo $this->content;
        return true;
    }

    public function outputErrors(){
        global $gJConfig;
        header('Content-Type: text/css;charset='.$gJConfig->defaultCharset);
        echo "/*\n";
        if($this->hasErrors()){
            foreach( $GLOBALS['gJCoord']->errorMessages  as $e){
               echo '['.$e[0].' '.$e[1].'] '.$e[2]." \t".$e[3]." \t".$e[4]."\n";
            }
        }else{
            echo "[unknow error]\n";
        }
        echo "\n*/";
    }
}
?>