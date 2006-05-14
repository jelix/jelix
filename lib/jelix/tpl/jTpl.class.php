<?php
/**
* @package     jelix
* @subpackage  jtpl
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jTpl {

    public $_vars = array ();
    public $_meta = array();

    public function __construct(){

    }

    public function assign ($name, $value = null){
        if(is_array($name)){
           foreach ($name as $key => $val) {
               $this->_vars[$key] = $val;
           }
        }else{
            $this->_vars[$name] = $value;
        }
    }

    public function append ($name, $value = null){
        if(is_array($name)){
           foreach ($name as $key => $val) {
               if(isset($this->_vars[$key]))
                  $this->_vars[$key] .= $val;
               else
                  $this->_vars[$key] = $val;
           }
        }else{
            if(isset($this->_vars[$name]))
               $this->_vars[$name] .= $value;
            else
               $this->_vars[$name] = $value;
        }
    }

    public function assignIfNone ($name, $value = null){
        if(is_array($name)){
           foreach ($name as $key => $val) {
               if(!isset($this->_vars[$key]))
                  $this->_vars[$key] = $val;
           }
        }else{
            if(!isset($this->_vars[$name]))
               $this->_vars[$name] = $value;
        }
    }


#ifndef JTPL_STANDALONE
    function assignZone($name, $zoneName, $params=array()){
        $this->_vars[$name] = jZone::processZone ($zoneName, $params);
    }
#endif
    /*
    function assignStatic($varName, $select){
        $this->_vars[$varName] =  $GLOBALS['COPIX']['COORD']->includeStatic ($select);
    }

    function assignTpl($varName, $select, $params=array()){
        $tpl = new CopixTpl();
        $tpl->_vars = $params;
        $this->_vars[$varName] =  $tpl->fetch($select);
    }
    */

    public function isAssigned ($name){
        return isset ($this->_vars[$name]);
    }

    public function get ($name){
        if (isset ($this->_vars[$name])){
            return $this->_vars[$name];
        }else{
            $return = null;
            return $return;
        }
    }

    public function getTemplateVars (){
        return $this->_vars;
    }

    public function meta($tpl){
        $this->getTemplate($tpl,'template_meta_');
    }

    public function display ($tpl){
        $this->getTemplate($tpl,'template_');
    }

    protected function  getTemplate($tpl,$fctname){
#ifndef JTPL_STANDALONE
        $sel = new jSelectorTpl($tpl);
        if(!$sel->isValid()){
            trigger_error (jLocale::get('jelix~errors.selector.invalid',$sel->toString(true)), E_USER_ERROR);
            return;
        }
        jIncluder::inc($sel);
        $fct = $fctname.md5($sel->module.'_'.$sel->resource);
#else
        $tpl = JTPL_TEMPLATES_PATH . $tpl;
        $filename = basename($tpl);
        $cachefile = JTPL_CACHE_PATH . $filename;

        $mustCompile = $GLOBALS['jTplConfig']['compilation_force']['force'] || !file_exists($cachefile);
        if (!$mustCompile) {
            if (filemtime($tpl) > filemtime($cachefile)) {
            $mustCompile = true;
            }
        }

        if ($mustCompile) {
            include_once(JTPL_PATH . 'jTplCompiler.class.php');

            $compiler = new jTplCompiler();
            $compiler->compile($tpl);
        }
        require_once($cachefile);
        $fct = $fctname.md5($tpl);
#endif
        $fct($this);
    }

    public function fetch ($tpl){
        ob_start ();
        try{
           $this->getTemplate($tpl,'template_');
           $content = ob_get_clean();
        }catch(Exception $e){
           ob_end_clean();
           throw $e;
        }
        return $content;
    }

}
?>
