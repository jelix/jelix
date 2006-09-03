<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
* class base for response object
* A response object is responsible to generate a content in a specific format.
* @package  jelix
* @subpackage core
*/
abstract class jResponse {
    /**
    * ident of the response type
    * @var string
    */
    protected  $_type = null;

    protected $_errorMessages=array();

    protected $_attributes = array();

    protected $_acceptSeveralErrors=true;

    protected $_httpHeaders = array();

    protected $_httpHeadersSent = false;

    protected $_httpStatusCode ='200';

    protected $_httpStatusMsg ='OK';

    /**
    * constructor
    */
    function __construct ($attributes=array()){
       $this->_attributes = $attributes;
    }

    /**
     * Send the response in the correct format.
     *
     * @return boolean    true if the output is ok
     * @internal should take care about errors
     */
    abstract public function output();

    /**
     * Send a response with only error messages which appears during the action
     * (errors, warning, notice, exceptions...). Type and error details
     *  depend on the application configuration
     */
    abstract public function outputErrors();


    public final function getType(){ return $this->_type;}
    public function getFormatType(){ return $this->_type;}
    public final function acceptSeveralErrors(){ return $this->_acceptSeveralErrors;}
    public final function hasErrors(){ return count($GLOBALS['gJCoord']->errorMessages)>0;}

    public function addHttpHeader($htype, $hcontent){ $this->_httpHeaders[$htype]=$hcontent;}

    /**
     * set the http status code for the http header
     * @param string $code  the status code (200, 404...)
     * @param string $msg the message following the status code ("OK", "Not Found"..)
     */
    public function setHttpStatus($code, $msg){ $this->_httpStatusCode=$code; $this->_httpStatusMsg=$msg;}

    /**
     *
     */
    protected function sendHttpHeaders(){
        header("HTTP/1.0 ".$this->_httpStatusCode.' '.$this->_httpStatusMsg);
        foreach($this->_httpHeaders as $ht=>$hc){
            header($ht.': '.$hc);
        }
        $this->_httpHeadersSent=true;
        /*
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        */
    }
}
?>