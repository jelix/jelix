<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author      Claudio Bernardes
 * @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
 *
 * @copyright   2012 Claudio Bernardes
 * @copyright   2006-2012 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * HTML form builder.
 *
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @see http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */
class button_htmlFormWidget extends \Jelix\Forms\HtmlWidget\WidgetBase
{
    protected function getCSSClass()
    {
        if (isset($this->attributes['class'])) {
            $class = $this->attributes['class'].' ';
        } else {
            $class = '';
        }

        $class .= 'jforms-ctrl-'.$this->ctrl->type;

        $attrClass = $this->ctrl->getAttribute('class');
        if ($attrClass) {
            $class .= ' '.$attrClass;
        }

        return $class;
    }

    public function outputControl()
    {
        $attr = $this->getControlAttributes();

        unset($attr['readonly']); //readonly is useless on button

        $attr['value'] = $this->getValue();
        echo '<button ';
        $this->_outputAttr($attr);
        echo '>',htmlspecialchars($this->ctrl->label),'</button>';
        $this->parentWidget->addJs('c=null;');
    }
}
