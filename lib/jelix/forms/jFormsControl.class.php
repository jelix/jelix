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

/**
 *
 * @package     jelix
 * @subpackage  forms
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

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlInput extends jFormsControl {
   public $type='input';
}


/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSelect1 extends jFormsControl {
   public $type="select1";
   public $datasource; // jIFormDatasource
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSelect extends jFormsControlSelect1 {
   public $type="select";
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlTextarea extends jFormsControl {
   public $type='textarea';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSecret extends jFormsControl {
   public $type='secret';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlOutput extends jFormsControl {
   public $type='output';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlUpload extends jFormsControl {
   public $type='upload';
}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlSubmit extends jFormsControl {
   public $type='submit';
}

?>