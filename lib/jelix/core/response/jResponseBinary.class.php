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
*/


/**
* générateur de sortie pour renvoyer des fichiers en download ou direct au navigateur
* @package  jelix
* @subpackage core
*/

final class jResponseBinary  extends jResponse {
    /**
    * identifiant du générateur de sortie
    * @var string
    */
    protected $_type = 'binary';

    /**
     * Chemin vers le fichier à envoyer. Vide si on envoie un contenu
     * @var string
     */
    public $fileName ='';
    /**
     * nom de fichier sous lequel il faut envoyer le contenu
     * @var string
     */
    public $outputFileName ='';

    /**
     * contenu à envoyer. Vide si on veut envoyer un fichier
     * @var string
     */
    public $content = null;

    /**
     * Indique si on veut forcer un téléchargement/"enregistrer sous" coté navigateur ou pas
     * si false, indiquer le bon type mime dans $mimetype
     * @var boolean
     */
    public $doDownload = true;

    /**
     * génère le contenu et l'envoi au navigateur.
     * Il doit tenir compte des erreurs
     * @return boolean    true si la génération est ok, false sinon
     */
    public function output(){
        if($this->doDownload){
            $this->mimeType = 'application/forcedownload';
            if (!strlen($this->outputFileName)){
                $f = explode ('/', str_replace ('\\', '/', $this->fileName));
                $this->outputFileName = $f[count ($f)-1];
            }
        }
        if($this->hasErrors()) return false;

        if($this->content === null){
            if (is_readable ($this->fileName) && is_file ($this->fileName)){
                header("Content-Type: ".$this->mimeType);
                if($this->doDownload) $this->_downloadHeader();
                header("Content-Length: ".filesize ($this->fileName));
                readfile ($this->fileName);
                flush();
                return true;
            }else
                return false;
        }else{
            header("Content-Type: ".$this->mimeType);
            if($this->doDownload) $this->_downloadHeader();
            header("Content-Length: ".strlen ($this->content));
            echo $this->content;
            flush();
            return true;
        }
    }

    private function _downloadHeader(){
        header("Content-Disposition: attachment; filename=".$this->outputFileName);
        header("Content-Description: File Transfert");
        header("Content-Transfer-Encoding: binary");
        header("Pragma: no-cache");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Expires: 0");
    }

    public function outputErrors(){

    }


}


?>