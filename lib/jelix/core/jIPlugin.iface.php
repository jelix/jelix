<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
* interface that should implement all coordinator plugins
*/

interface jIPlugin {

    /**
     * @param    array  $config  list of configuration parameters
     */
    public function __construct($config);

    /**
     * @param    jAction  $action  action that will be executed
     */
    public function beforeAction(& $action);

    /**
     *
     */
    public function beforeOutput();

    public function afterProcess ();
}
?>