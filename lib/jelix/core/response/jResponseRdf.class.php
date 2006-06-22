<?php
/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
* Genérateur de réponse RDF
* @see jResponse
*/

final class jResponseRdf extends jResponse {
    /**
    * identifiant du générateur
    * @var string
    */
    protected $_type = 'rdf';
    protected $_acceptSeveralErrors=true;

    public $resNs="http://dummy/rdf#";
    public $resNsPrefix='row';
    public $resUriPrefix = "urn:data:row:";
    public $resUriRoot = "urn:data:row";
    public $datas;
    public $asAttribute=array();
    public $asElement=array();

    public function output(){
        if($this->hasErrors()) return false;
        header("Content-Type: text/xml;charset=".$GLOBALS['gJConfig']->defaultCharset);
        $this->generateContent();
        return true;
    }

    public  function fetch(){
        if($this->hasErrors()) return false;
        ob_start();
        $this->generateContent();
        $content= ob_get_contents();
        ob_end_clean();
        return $content;
    }

    protected function generateContent(){
        $EOL="\n";
        echo '<?xml version="1.0" encoding="ISO-8859-1"?>'.$EOL;
        echo '<RDF xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'.$EOL;
        echo '  xmlns:'.$this->resNsPrefix.'="'.$this->resNs.'"  xmlns:NC="http://home.netscape.com/NC-rdf#">'.$EOL;

        echo '<Bag about="'.$this->resUriRoot.'">'.$EOL;
        foreach($this->datas as $dt){
            echo "<li>\n";
            echo "<Description ";
            // NC:parseType="Integer"
            if(is_object($dt))
                $dt=get_object_vars ($dt);
            if(count($this->asAttribute) || count($this->asElement)){
                foreach($this->asAttribute as $name){
                    echo $this->resNsPrefix.':'.$name.'="'.$this->xmlEntities($dt[$name]).'" ';
                }
                echo ">\n";
                foreach($this->asElement as $name){
                    echo '<'.$this->resNsPrefix.':'.$name.'>'.$this->xmlEntities($dt[$name]).'</'.$this->resNsPrefix.':'.$name.">\n";
                }

            }else{
                echo ">\n";
                foreach($dt as $name=>$val){
                    echo '<'.$this->resNsPrefix.':'.$name.'>'.$this->xmlEntities($val).'</'.$this->resNsPrefix.':'.$name.">\n";
                }
            }
            echo "</Description>\n";
            echo "</li>\n";
        }
        echo "</Bag>\n";
        echo "</RDF>\n";
    }


    public function outputErrors(){
        global $gJCoord;

        header("Content-Type: text/xml;charset=".$GLOBALS['gJConfig']->defaultCharset);
        $EOL="\n";
        echo '<?xml version="1.0" encoding="ISO-8859-1"?>'.$EOL;
        echo '<RDF xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'.$EOL;
        echo '  xmlns:err="http://jelix.org/ns/rdferr#"  xmlns:NC="http://home.netscape.com/NC-rdf#">'.$EOL;

        echo '<Bag about="urn:jelix:error">'.$EOL;
        if(count($gJCoord->errorMessages)){
           foreach($gJCoord->errorMessages as $e){
                echo "<li>\n";
                echo '<Description err:code="'.$e[1].'" err:type="'.$e[0].'" err:file="'.$e[3].'" err:line="'.$e[4].'">';
                echo '<err:message>'.$this->xmlEntities($e[2]).'</err:message>';
                echo "</Description>\n";
                echo "</li>\n";
           }
        }else{
            echo "<li>\n";
            echo '<Description err:code="-1" err:type="error" err:file="" err:line="">';
            echo '<err:message>Unknow error</err:message>';
            echo "</Description>\n";
            echo "</li>\n";
        }
        echo "</Bag>\n";
        echo "</RDF>\n";
    }

    protected function xmlEntities($str){
        return preg_replace(array("'&'", "'\"'", "'<'", "'>'"), array('&#38;', '&#34;','&lt;', '&gt;'), $str);
    }



}

?>