<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 *
 */
include JELIX_LIB_UTILS_PATH.'jZipCreator.class.php';

/**
* generate a zip content and send it to the browser
* @package  jelix
* @subpackage core_response
*/
class jResponseZip extends jResponse {
    /**
    * @var string
    */
    protected $_type = 'zip';

    /**
     * the zip content. Manipulates it to add files into it
     * @var jZipCreator
     */
    public $content = null;

    /**
     * file name which appear in the browser
     */
    public $zipFilename='';

    /**
    * constructor
    */
    function __construct ($attributes=array()){
        $this->content = new jZipCreator();
        parent::__construct($attributes);
    }

    /**
     * construct the zip content into zip format, and send it to the browser
     * @return boolean    true  if it's ok
     */
    public function output(){
        $zipContent = $this->content->getContent();
        $this->_httpHeaders['Content-Type']='application/zip';
        $this->_httpHeaders['Content-Disposition']='attachment; filename="'.$this->zipfilename.'"';
        $this->_httpHeaders['Content-Description']='File Transfert';
        $this->_httpHeaders['Content-Transfer-Encoding']='binary';
        $this->_httpHeaders['Pragma']='no-cache';
        $this->_httpHeaders['Cache-Control']='no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
        $this->_httpHeaders['Expires']='0';
        $this->_httpHeaders['Content-length']=strlen($zipContent);
        $this->sendHttpHeaders();
        echo $zipContent;
        flush();
        return true;
    }

    public function outputErrors(){
        global $gJConfig;
        header('Content-Type: text/plain;charset='.$gJConfig->defaultCharset);
        if($this->hasErrors()){
            foreach( $GLOBALS['gJCoord']->errorMessages  as $e){
               echo '['.$e[0].' '.$e[1].'] '.$e[2]." \t".$e[3]." \t".$e[4]."\n";
            }
        }else{
            echo "[unknow error]\n";
        }
    }
}
?>