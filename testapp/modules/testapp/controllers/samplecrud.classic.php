<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 *
 */
class sampleCrudCtrl extends jControllerDaoCrud {

    protected $listPageSize = 5;

    protected $dao = 'testapp~products';

    protected $form = 'testapp~products';


    protected $propertiesForRecordsOrder = array('name'=>'asc',
                                                 'price'=>'asc', 'publish_date'=>'desc');

    protected function _getResponse(){
        $rep = parent::_getResponse();
        $rep->addCSSLink(jApp::urlBasePath().'jelix/design/records_list.css');
        return $rep;
    }
}
