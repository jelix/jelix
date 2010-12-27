<?php
/**
* @package
* @subpackage testapp
* @author
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

class defaultCtrl extends jControllerCmdLine {
    protected $allowed_options = array(
            'index' => array());

    protected $allowed_parameters = array(
            'index' => array(),
            'other'=>array('aaa'=>true,'bbb'=>false)
            );


    public $help = array('index'=>'',
                         'other'=>'Just display a message, it accepts a first parameter and an optional second one');

    /**
    *
    */
    function index() {
        $rep = $this->getResponse();
        $rep->addContent("Hello, it works !");
        return $rep;
    }

    /**
    *
    */
    function other() {
        $rep = $this->getResponse();
        $rep->addContent("given parameters:\naaa=".$this->param('aaa')."\nbbb=".$this->param('bbb')."\n");
        return $rep;
    }

}

