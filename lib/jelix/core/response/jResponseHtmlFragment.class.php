<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire
* @copyright   2008 Tahina Ramaroson, Sylvain de Vathaire
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Send Html part
 * @package  jelix
 * @subpackage core_response
 */
class jResponseHtmlFragment extends jResponse {

    /**
    * jresponse id
    * @var string
    */
    protected $_type = 'htmlfragment';

    /**
    * template selector
    * set the template name in this property
    * @var string
    */
    public $tplname = '';

    /**
    * the jtpl object created automatically
    * @var jTpl
    */
    public $tpl = null;

    /**
    * constructor;
    * setup the template engine
    */
    function __construct (){
        $this->tpl = new jTpl();
        parent::__construct();
    }

    /**
    * send the Html part
    * @return boolean    true if it's ok
    */
    final public function output(){

        global $gJConfig;

        if($this->hasErrors()) return false;

        $this->doAfterActions();

        if($this->hasErrors()) return false;

        $content = '';
        if($this->tplname!=''){
            $content=$this->tpl->fetch($this->tplname,'html');
            if($this->hasErrors()) return false;
        }

        $this->_httpHeaders['Content-Type']='text/plain;charset='.$gJConfig->charset;
        $this->_httpHeaders['Content-length']=strlen($content);
        $this->sendHttpHeaders();
        echo $content;
        return true;
    }

    /**
     * The method you can overload in your inherited htmlfragment response
     * after all actions
     * @since 1.1
     */
    protected function doAfterActions(){
        $this->_commonProcess(); // for compatibility with jelix 1.0
    }

    /**
     * same use as doAfterActions, but deprecated method. It is just here for compatibility with Jelix 1.0.
     * Use doAfterActions instead
     * @deprecated
     */
    protected function _commonProcess(){
    }

    /**
     * output errors
     */
    final public function outputErrors(){

        global $gJConfig;
        $this->clearHttpHeaders();
        $this->_httpStatusCode ='500';
        $this->_httpStatusMsg ='Internal Server Error';
        $this->_httpHeaders['Content-Type']='text/plain;charset='.$gJConfig->charset;

        if($this->hasErrors()){
            $content = $this->getFormatedErrorMsg();
        }else{
            $content = '<p style="color:#FF0000">Unknow Error</p>';
        }

        $this->_httpHeaders['Content-length'] = strlen($content);
        $this->sendHttpHeaders();
        echo $content;
    }

    /**
     * create html error messages
     * @return string html content
     */
    protected function getFormatedErrorMsg(){
        $errors='';
        foreach( $GLOBALS['gJCoord']->errorMessages  as $e){
           $errors .=  '<p style="margin:0;"><b>['.$e[0].' '.$e[1].']</b> <span style="color:#FF0000">'.htmlspecialchars($e[2], ENT_NOQUOTES, $this->_charset)."</span> \t".$e[3]." \t".$e[4]."</p>\n";
        }
        return $errors;
    }
}
?>