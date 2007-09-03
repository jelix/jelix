<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 *     NOT FINISHED
 */
class sampleCrudCtrl extends jControllerDaoCrud {

    protected $listPageSize = 5;

    protected $dao = 'jelix_tests~products';

    protected $form = 'testapp~products';

}

?>