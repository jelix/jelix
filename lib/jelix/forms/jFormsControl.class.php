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
   public $type = null;
   public $ref='';
   public $datatype='string';
   public $required=false;
   public $readonly=false;
   public $label='';
   public $labellocal='';

   public $value='';
   public $defaultValue='';
//  public $pattern =null;

   function __construct($ref){
      $this->ref = $ref;
   }
}

class jFormsControlInput extends jFormsControl {
   public $type='input';
}


class jFormsControlSelect1 extends jFormsControl {
   public $type="select1";
   public $datasource; // jIFormDatasource
}




?>