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
 class output_htmlFormWidget extends \Jelix\Forms\HtmlWidget\WidgetBase
 {
     public function outputControl()
     {
         $attr = $this->getControlAttributes();

         $outputAttr = array(
             'class' => 'jforms-value '.$attr['class'],
         );

         unset($attr['readonly'], $attr['class']);

         if (isset($attr['title'])) {
             $outputAttr['title'] = $attr['title'];
             unset($attr['title']);
         }

         $attr['type'] = 'hidden';
         $attr['value'] = $this->getValue();
         echo '<input';
         $this->_outputAttr($attr);
         echo '/>';
         $this->displayControl($outputAttr, $attr['value']);
         $this->parentWidget->addJs('c=null;');
     }

     protected function displayControl($outputAttr, $value)
     {
         echo '<span ';
         $this->_outputAttr($outputAttr);
         echo '>',htmlspecialchars($value===null?'':$value),"</span>\n";
     }
 }
