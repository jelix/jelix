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
* Response use to send a binary file to the browser
* @package  jelix
* @subpackage core
*/

final class jResponseBinary  extends jResponse {
    /**
    * @var string
    */
    protected $_type = 'binary';

    /**
     * The path of the file you want to send. Keep empty if you provide the content
     * @var string
     */
    public $fileName ='';
    /**
     * name of the file under which the content will be send to the user
     * @var string
     */
    public $outputFileName ='';

    /**
     * the content you want to send. Keep empty if you indicate a filename
     * @var string
     */
    public $content = null;

    /**
     * Says if the "save as" dialog appear or not to the user.
     * if false, specify the mime type in $mimetype
     * @var boolean
     */
    public $doDownload = true;

    /**
     * send the content or the file to the browser.
     * @return boolean    true it it's ok
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

    /**
     * @todo do this method
     */
    public function outputErrors(){

    }
}
?>