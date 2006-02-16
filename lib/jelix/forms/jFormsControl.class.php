<?php
/**
* @package     jelix
* @subpackage  forms
* @version     $Id:$
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


abstract class jFormsControl {
   public $name;
   public $type = null;
   public $ref;
   public $refdao;
   public $datatype;
   public $required;
   public $label;
   public $labellocal;

   public $value;
   public $defaultValue;

//   var $regexp       =null;   // expression regulière

}

class jFormsControlInput extends jFormsControl {
   public $type='input';
}


class jFormsControlSelect1 extends jFormsControl {
   public $type="select1";
   public $datasource; // jIFormDatasource
}




?>