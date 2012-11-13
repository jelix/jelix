<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @copyright   2006-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace jelix\forms\HtmlWidget;

class RootWidget implements ParentWidgetInterface {

    //------ ParentBuilderInterface

    protected $js = '';
    function addJs($js) {
        $this->js .= $js;
    }

    protected $finalJs = '';
    function addFinalJs($js) {
        $this->finalJs .= $js;
    }

    //------ Other methods
}

