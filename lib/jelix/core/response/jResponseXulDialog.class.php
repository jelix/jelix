<?php
/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
*
*/
require_once(JELIX_LIB_RESPONSE_PATH.'jResponseXul.class.php');

/**
* Genérateur de réponse XUL dialog
* @package  jelix
* @subpackage core
* @see jResponseXul
*/
class jResponseXulDialog extends jResponseXul {
    protected $_type =  'xuldialog';
    protected $_root = 'dialog';
}
?>