<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Julien Issler
* @contributor Uriel Corfa, Laurent Jouannneau
* @copyright   2007 Julien Issler, 2007 Emotic SARL, 2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.0
*/

/**
 *
 */
require_once (JELIX_LIB_UTILS_PATH.'jTcpdf.class.php');

/**
* PDF Response based on TCPDF (http://tcpdf.sourceforge.net) 
* @package  jelix
* @subpackage core_response
* @since 1.0
*/
class jResponseTcpdf  extends jResponse {
    /**
    * @var string
    */
    protected $_type = 'tcpdf';

    /**
     * the tcpdf object you want to send.
     * @var object
     */
    public $tcpdf = null;

    /**
     * name of the file under which the file will be send to the user
     * @var string
     */
    public $outputFileName = 'document.pdf';

    /**
     * Says if the "save as" dialog appear or not to the user.
     * @var boolean
     */
    public $doDownload = false;

    /**
     * send the PDF to the browser.
     * @return boolean    true if it's ok
     */
    public function output(){

        if($this->hasErrors()) return false;

        if(!($this->tcpdf instanceof jTcpdf)){
            throw new jException('jelix~errors.reptcpdf.not_a_jtcpdf');
            return false;
        }

        if($this->doDownload)
            $this->tcpdf->Output($this->outputFileName,'D');
        else
            $this->tcpdf->Output($this->outputFileName,'I');

        flush();
        return true;
    }

    /**
     *
     */
    public function outputErrors(){
        global $gJConfig;
        $this->clearHttpHeaders();
        $this->_httpStatusCode ='500';
        $this->_httpStatusMsg ='Internal Server Error';
        $this->addHttpHeader('Content-Type','text/plain;charset='.$gJConfig->charset,false);
        $this->sendHttpHeaders();
        if($this->hasErrors()){
            foreach( $GLOBALS['gJCoord']->errorMessages  as $e){
               echo '['.$e[0].' '.$e[1].'] '.$e[2]." \t".$e[3]." \t".$e[4]."\n";
            }
        }else{
            echo "[unknow error]\n";
        }
    }


    /**
    * Creates the TCPDF object in $this->fpdf
    * @param string $orientation Orientation (portrait/landscape)
    * @param string $unit Page base unit (default to millimeters)
    * @param mixed $format Page size (defaults to A4)
    * @param String $encoding charset encoding;
    */
    public function initPdf($orientation='P', $unit='mm', $format='A4', $encoding=null){
        $this->tcpdf = new jTcpdf($orientation, $unit, $format, $encoding);
    }

    /**
    * Transmits calls to non-existent methods to TCPDF (max : 8 params because
    * TCPDF methods never take more than 8 params)
    * @param string $method Method name
    * @param array $attr Method parameters
    * @return mixed Value returned bu FPDF's method
    */
    public function __call($method, $attr){
        if ($this->tcpdf !== null){
            return call_user_func_array(array($this->tcpdf, $method), $attr );
        }else{
            throw new jException('jelix~errors.reptcpdf.not_a_jtcpdf');
            return false;
        }
    }

}
?>