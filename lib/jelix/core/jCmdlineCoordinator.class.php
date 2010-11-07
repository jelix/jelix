<?php
/**
* @package      jelix
* @subpackage   core
* @author       Christophe Thiriot
* @copyright    2008 Christophe Thiriot
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * The command line version of jCoordinator 
 *
 * This allows us to handle exit code of commands properly
 * @package  jelix
 * @subpackage core
 */
class jCmdlineCoordinator extends jCoordinator{
    /**
    * main method : launch the execution of the action.
    *
    * This method should be called in a Command line entry point.
    * @param  jRequestCmdline  $request the command line request object
    */
    public function process($request){
        parent::process($request);
        exit($this->response->getExitCode());
    }
}
