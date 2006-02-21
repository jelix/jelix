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
   public $labellocale='';

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

class jFormsControlSelect extends jFormsControlSelect1 {
   public $type="select";
}

class jFormsControlTextarea extends jFormsControl {
   public $type='textarea';
}

class jFormsControlSecret extends jFormsControl {
   public $type='secret';
}

class jFormsControlOutput extends jFormsControl {
   public $type='output';
}
class jFormsControlUpload extends jFormsControl {
   public $type='upload';
}

class jFormsControlSubmit extends jFormsControl {
   public $type='submit';
}

?>