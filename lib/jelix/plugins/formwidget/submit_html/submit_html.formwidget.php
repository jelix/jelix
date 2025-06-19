<?php
/**
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
class submit_htmlFormWidget extends \Jelix\Forms\HtmlWidget\WidgetBase
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

        return $class.' jforms-submit';
    }

    public function outputControl()
    {
        $attr = $this->getControlAttributes();

        unset($attr['readonly']);

        $attr['type'] = 'submit';

        if ($this->ctrl->standalone) {
            $attr['value'] = $this->ctrl->label;
            echo '<input';
            $this->_outputAttr($attr);
            echo "/>\n";
        } else {
            $id = $this->builder->getName().'_'.$this->ctrl->ref.'_';
            $attr['name'] = $this->ctrl->ref;
            foreach ($this->ctrl->datasource->getData($this->builder->getForm()) as $v => $label) {
                // because IE6 sucks with <button type=submit> (see ticket #431), we must use input :-(
                $attr['value'] = $label;
                $attr['id'] = $id.$v;
                echo ' <input';
                $this->_outputAttr($attr);
                echo '/>';
            }
            echo "\n";
        }
    }
}
